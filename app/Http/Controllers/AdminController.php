<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Staff;
use App\Models\Payroll;
use App\Models\Expenditure;
use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\FeeStructure;
use App\Models\StudentFee;
use App\Models\AdditionalCharge;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Announcement;
use App\Models\FinancialAuditLog;
use App\Services\FeeAllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin');
    }

    public function dashboard()
    {
        $currentSession = AcademicSession::where('is_active', true)->first();
        
        $stats = [
            'total_students' => Student::count(),
            'new_intake_count' => Student::whereIn('category', ['NI', 'NEW_INTAKE', 'NEW'])->count(),
            'returning_count' => Student::whereIn('category', ['OS', 'RETURNING', 'OLD'])->count(),
            'total_staff' => User::where('role_id', '!=', Role::where('role_name', 'Student')->first()->id ?? 4)->count(),
            'active_sessions' => AcademicSession::where('is_active', true)->count(),
            'pending_admissions' => Student::where('admission_status', 'pending')->count(),
            'total_expected_fees' => StudentFee::sum('amount_due'),
            'total_collected_fees' => StudentFee::sum('amount_paid'),
            'total_outstanding_fees' => StudentFee::sum('balance'),
            'fully_paid_count' => StudentFee::where('status', 'paid')->count(),
            'partially_paid_count' => StudentFee::whereIn('status', ['partial', 'partially_paid'])->count(),
            'unpaid_count' => StudentFee::whereIn('status', ['unpaid', 'pending'])->count(),
        ];

        return view('admin.dashboard.index', compact('stats', 'currentSession'));
    }

    // Student Management
    public function students()
    {
        $students = Student::with(['currentClass', 'currentSession', 'currentTerm', 'studentFees'])->paginate(20);
        return view('admin.students.index', compact('students'));
    }

    public function createStudent()
    {
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();
        $terms = Term::all();
        return view('admin.students.create', compact('classes', 'sessions', 'terms'));
    }

    public function storeStudent(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other,Male,Female',
            'admission_status' => 'required|in:pending,admitted,withdrawn',
            'category' => 'required|in:NI,OS,NEW_INTAKE,RETURNING',
            'department' => 'nullable|string|in:general,science,arts,commercial',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_class_id' => 'nullable|exists:school_classes,id',
            'current_session_id' => 'nullable|exists:academic_sessions,id',
            'current_term_id' => 'nullable|exists:terms,id',
        ]);

        // CRITICAL CHECK: Duplicate student warning for New Intake
        if ($request->category === 'NI' && !$request->has('confirm_duplicate')) {
            $existing = Student::where('first_name', $request->first_name)
                ->where('last_name', $request->last_name)
                ->where('date_of_birth', $request->date_of_birth)
                ->first();

            if ($existing) {
                return back()->withInput()->with('duplicate_warning', [
                    'message' => 'Possible Existing Student Found! A student named ' . $existing->first_name . ' ' . $existing->last_name . ' (DOB: ' . $existing->date_of_birth->format('Y-m-d') . ') already exists.',
                    'student_id' => $existing->id
                ]);
            }
        }

        $data = $request->all();

        if ($request->hasFile('passport')) {
            $data['passport'] = $request->file('passport')->store('passports', 'public');
        }

        $cid = $request->current_class_id ?? $request->class_id ?? $request->class_link;
        if (empty($data['admission_no'])) {
            $data['admission_no'] = generate_student_admission_no($cid);
        }
        if (empty($data['student_number'])) {
            $data['student_number'] = $data['admission_no'];
        }
        $data['enrollment_date'] = now();

        $student = Student::create($data);

        // Auto-allocate fee structure
        FeeAllocationService::allocateFeeForStudent($student);

        FinancialAuditLog::logAction('created_student', $student, [], $student->toArray());

        return redirect()->route('admin.students')->with('success', 'Student created successfully and fee allocated automatically.');
    }

    public function showStudent(Student $student)
    {
        $student->load(['currentClass', 'currentSession', 'currentTerm', 'studentFees.feeStructure', 'studentFees.payments', 'additionalCharges']);
        return view('admin.students.show', compact('student'));
    }

    public function admissionLetter(Student $student)
    {
        $schoolSettings = SchoolSetting::first();
        return view('admin.students.admission_letter', compact('student', 'schoolSettings'));
    }

    public function editStudent(Student $student)
    {
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();
        $terms = Term::all();
        return view('admin.students.edit', compact('student', 'classes', 'sessions', 'terms'));
    }

    public function updateStudent(Request $request, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other,Male,Female',
            'admission_status' => 'required|in:pending,admitted,withdrawn',
            'category' => 'required|in:NI,OS,NEW_INTAKE,RETURNING',
            'department' => 'nullable|string|in:general,science,arts,commercial',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_class_id' => 'nullable|exists:school_classes,id',
            'current_session_id' => 'nullable|exists:academic_sessions,id',
            'current_term_id' => 'nullable|exists:terms,id',
        ]);

        $data = $request->all();

        if ($request->hasFile('passport')) {
            if ($student->passport) {
                Storage::disk('public')->delete($student->passport);
            }
            $data['passport'] = $request->file('passport')->store('passports', 'public');
        }

        $oldData = $student->toArray();
        $student->update($data);

        FinancialAuditLog::logAction('updated_student', $student, $oldData, $student->toArray());

        return redirect()->route('admin.students')->with('success', 'Student updated successfully.');
    }

    public function destroyStudent(Student $student)
    {
        if ($student->passport) {
            Storage::disk('public')->delete($student->passport);
        }
        $student->delete();
        return redirect()->route('admin.students')->with('success', 'Student deleted successfully.');
    }

    // Student Promotions
    public function promotions()
    {
        $classes = SchoolClass::all();
        $sessions = AcademicSession::all();
        $terms = Term::orderBy('term_number')->get();
        return view('admin.students.promotions', compact('classes', 'sessions', 'terms'));
    }

    public function processPromotions(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:school_classes,id',
            'to_class_id' => 'required|exists:school_classes,id',
            'to_session_id' => 'required|exists:academic_sessions,id',
            'to_term_id' => 'required|exists:terms,id',
        ]);

        $students = Student::where('current_class_id', $request->from_class_id)
            ->where('admission_status', 'admitted')
            ->get();

        $promotedCount = 0;
        foreach ($students as $student) {
            // Promoted students move to RETURNING ('OS')
            $student->category = 'OS';
            $student->current_class_id = $request->to_class_id;
            $student->current_session_id = $request->to_session_id;
            $student->current_term_id = $request->to_term_id;
            $student->save();

            // Auto-allocate fee for returning session/term
            FeeAllocationService::allocateFeeForStudent($student, $request->to_session_id, $request->to_term_id);

            $promotedCount++;
        }

        return redirect()->route('admin.students.promotions')->with('success', "Successfully promoted {$promotedCount} students. Fees allocated for returning session.");
    }

    // Fees Management
    public function fees()
    {
        $fees = FeeStructure::with(['session', 'term', 'schoolClass'])->paginate(20);
        return view('admin.fees.index', compact('fees'));
    }

    public function createFee()
    {
        $sessions = AcademicSession::all();
        $terms = Term::all();
        $classes = SchoolClass::all();
        return view('admin.fees.create', compact('sessions', 'terms', 'classes'));
    }

    public function storeFee(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:one_time,monthly,quarterly,semi_annual,annual',
            'fee_type' => 'required|in:' . implode(',', schema_enum_values('fee_structures', 'fee_type')),
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'category' => 'required|in:NI,OS',
            'gender' => 'required|in:M,F,All',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $category = $request->category;
        $termId = $category === 'NI' ? null : ($request->term_id ?: null);

        // Prevent duplicate fee structure for exact same name, fee type, session, class, gender, category, and term
        $duplicateQuery = FeeStructure::where('session_id', $request->session_id)
            ->where('class_id', $request->class_id)
            ->where('gender', $request->gender)
            ->where('category', $category)
            ->where('fee_type', $request->fee_type)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $request->name))])
            ->where('is_active', true);

        if ($category === 'OS') {
            if ($termId) {
                $duplicateQuery->where('term_id', $termId);
            } else {
                $duplicateQuery->whereNull('term_id');
            }
        }

        if ($duplicateQuery->exists()) {
            return back()->withInput()->withErrors(['duplicate' => "An active fee structure with this exact name ('{$request->name}') and fee type ('{$request->fee_type}') already exists for this combination."]);
        }

        $feeData = $request->all();
        $feeData['term_id'] = $termId;

        $feeStructure = FeeStructure::create($feeData);

        FinancialAuditLog::logAction('created_fee_structure', $feeStructure, [], $feeStructure->toArray());

        return redirect()->route('admin.fees')->with('success', 'Fee structure created successfully.');
    }

    public function editFee(FeeStructure $fee)
    {
        $sessions = AcademicSession::all();
        $terms = Term::all();
        $classes = SchoolClass::all();
        return view('admin.fees.edit', compact('fee', 'sessions', 'terms', 'classes'));
    }

    public function updateFee(Request $request, FeeStructure $fee)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'frequency' => 'required|in:one_time,monthly,quarterly,semi_annual,annual',
            'fee_type' => 'required|in:' . implode(',', schema_enum_values('fee_structures', 'fee_type')),
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'category' => 'required|in:NI,OS',
            'gender' => 'required|in:M,F,All',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $category = $request->category;
        $termId = $category === 'NI' ? null : ($request->term_id ?: null);

        $duplicateQuery = FeeStructure::where('session_id', $request->session_id)
            ->where('class_id', $request->class_id)
            ->where('gender', $request->gender)
            ->where('category', $category)
            ->where('fee_type', $request->fee_type)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim((string) $request->name))])
            ->where('is_active', true)
            ->where('id', '!=', $fee->id);

        if ($category === 'OS') {
            if ($termId) {
                $duplicateQuery->where('term_id', $termId);
            } else {
                $duplicateQuery->whereNull('term_id');
            }
        }

        if ($duplicateQuery->exists()) {
            return back()->withInput()->withErrors(['duplicate' => "An active fee structure with this exact name ('{$request->name}') and fee type ('{$request->fee_type}') already exists for this combination."]);
        }

        $oldData = $fee->toArray();
        $feeData = $request->all();
        $feeData['term_id'] = $termId;

        $fee->update($feeData);

        FinancialAuditLog::logAction('updated_fee_structure', $fee, $oldData, $fee->toArray());

        return redirect()->route('admin.fees')->with('success', 'Fee structure updated successfully.');
    }

    public function destroyFee(FeeStructure $fee)
    {
        $oldData = $fee->toArray();
        $fee->delete();

        FinancialAuditLog::logAction('deleted_fee_structure', null, $oldData, []);

        return redirect()->route('admin.fees')->with('success', 'Fee structure deleted successfully.');
    }

    public function storeAdditionalCharge(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'fee_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);

        FeeAllocationService::addAdditionalCharge(
            $student,
            $request->fee_name,
            (float) $request->amount,
            $request->reason
        );

        return back()->with('success', 'Additional charge added successfully to student profile.');
    }

    // Excel Student Import
    public function importStudents(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt,xlsx,xls']);

        require_once base_path('db/functions.php');
        require_once base_path('db/school_portal_helpers.php');

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        $totalRecords = 0;
        $importedCount = 0;
        $failedRecords = [];

        $classes = SchoolClass::all()->keyBy('class_name');
        $sessions = AcademicSession::all()->keyBy('session_name');
        $activeSession = AcademicSession::where('is_active', true)->first();

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            $totalRecords++;

            $studentId = trim((string) ($data[0] ?? ''));
            $firstName = trim((string) ($data[1] ?? ''));
            $middleName = trim((string) ($data[2] ?? ''));
            $lastName = trim((string) ($data[3] ?? ''));
            $gender = trim((string) ($data[4] ?? ''));
            $className = trim((string) ($data[5] ?? ''));
            $studentTypeRaw = trim((string) ($data[6] ?? 'OS'));
            $sessionName = trim((string) ($data[7] ?? ''));
            $parentName = trim((string) ($data[8] ?? ''));
            $parentPhone = trim((string) ($data[9] ?? ''));

            // Validation checks
            $errors = [];
            if (empty($firstName)) $errors[] = 'First Name is required';
            if (empty($lastName)) $errors[] = 'Last Name is required';
            
            $genderClean = strtolower($gender);
            if (!in_array($genderClean, ['male', 'female', 'm', 'f'])) {
                $errors[] = 'Invalid Gender (Must be Male or Female)';
            } else {
                $genderClean = in_array($genderClean, ['male', 'm']) ? 'male' : 'female';
            }

            // All students on import are categorised as returning students (OS)
            $categoryClean = 'OS';

            $classObj = !empty($className) ? ($classes->get($className) ?? SchoolClass::where('class_name', 'LIKE', "%$className%")->first()) : null;
            if (!$classObj && !empty($className)) {
                $errors[] = "Class '$className' not found";
            }

            $sessionObj = !empty($sessionName) ? ($sessions->get($sessionName) ?? AcademicSession::where('session_name', 'LIKE', "%$sessionName%")->first()) : $activeSession;

            // Check duplicate email / student ID
            if (!empty($studentId) && Student::where('admission_no', $studentId)->orWhere('student_number', $studentId)->exists()) {
                $errors[] = "Duplicate Student ID '$studentId'";
            }

            if (!empty($errors)) {
                $failedRecords[] = [
                    'row' => $totalRecords,
                    'student_id' => $studentId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'reason' => implode('; ', $errors),
                ];
                continue;
            }

            try {
                $admissionNo = !empty($studentId) ? $studentId : generate_student_admission_no($classObj ? $classObj->id : null);
                $email = generate_student_school_email($firstName, $lastName);

                $user = User::create([
                    'username' => $admissionNo,
                    'name' => trim("{$lastName} {$firstName} {$middleName}"),
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role_id' => 4,
                    'status' => 'active',
                ]);

                $student = Student::create([
                    'user_link' => $user->id,
                    'admission_no' => $admissionNo,
                    'student_number' => $admissionNo,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'other_names' => $middleName,
                    'email' => $email,
                    'gender' => $genderClean,
                    'category' => $categoryClean,
                    'current_class_id' => $classObj ? $classObj->id : null,
                    'current_session_id' => $sessionObj ? $sessionObj->id : null,
                    'status' => 'active',
                    'admission_status' => 'admitted',
                    'date_of_birth' => now()->subYears(10),
                    'enrollment_date' => now(),
                ]);

                FeeAllocationService::allocateFeeForStudent($student);
                $importedCount++;

            } catch (\Throwable $e) {
                $failedRecords[] = [
                    'row' => $totalRecords,
                    'student_id' => $studentId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'reason' => 'Database error: ' . $e->getMessage(),
                ];
            }
        }
        fclose($handle);

        $failedCount = count($failedRecords);
        if ($failedCount > 0) {
            session(['failed_imports' => $failedRecords]);
        }

        return redirect()->route('admin.students')->with('import_summary', [
            'total' => $totalRecords,
            'imported' => $importedCount,
            'failed' => $failedCount,
        ]);
    }

    public function downloadFailedImports()
    {
        $failed = session('failed_imports', []);

        return response()->streamDownload(function() use ($failed) {
            echo "Row,Student ID,First Name,Last Name,Reason\n";
            foreach ($failed as $row) {
                echo "\"{$row['row']}\",\"{$row['student_id']}\",\"{$row['first_name']}\",\"{$row['last_name']}\",\"{$row['reason']}\"\n";
            }
        }, 'failed_student_imports.csv');
    }

    public function importActualPayments(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file',
            'session_id' => 'nullable|integer',
            'term_id' => 'nullable|integer',
            'fee_type' => 'nullable|string',
            'payment_method' => 'nullable|string',
            'payment_date' => 'nullable|date',
        ]);

        require_once base_path('db/functions.php');
        require_once base_path('db/school_portal_helpers.php');

        $activeSession = AcademicSession::where('is_active', true)->first();
        $sessionId = (int) ($request->session_id ?: ($activeSession ? $activeSession->id : 2));
        $termId = (int) ($request->term_id ?: 1);
        $targetFeeType = trim((string) ($request->input('fee_type') ?? 'tuition')) ?: 'tuition';
        $paymentMethod = $request->payment_method ?: 'bank_transfer';
        $paymentDate = $request->payment_date ?: now()->toDateString();

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        $firstLine = file_get_contents($filePath, false, null, 0, 500);

        $delimiter = ',';
        if ($firstLine !== false) {
            if (substr_count($firstLine, ';') > substr_count($firstLine, ',')) {
                $delimiter = ';';
            } elseif (substr_count($firstLine, "\t") > substr_count($firstLine, ',')) {
                $delimiter = "\t";
            }
        }

        $handle = fopen($filePath, 'r');
        $totalProcessed = 0;
        $successCount = 0;
        $ignoredCount = 0;
        $totalPaidAmount = 0.0;
        $importedRows = [];
        $failedRows = [];

        // Detect column positions
        $headerRow = fgetcsv($handle, 0, $delimiter);
        $snCol = 0;
        $nameCol = 1;
        $paidCol = 2;
        $hasHeader = false;

        if ($headerRow) {
            if (isset($headerRow[0])) {
                $headerRow[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $headerRow[0]);
            }

            foreach ($headerRow as $idx => $heading) {
                $clean = strtolower(trim((string) $heading));
                if (in_array($clean, ['sn', 's/n', 'id', 'no', 's_n'], true)) {
                    $snCol = $idx;
                    $hasHeader = true;
                } elseif (in_array($clean, ['name', 'student_name', 'student name', 'fullname', 'full_name', 'names'], true)) {
                    $nameCol = $idx;
                    $hasHeader = true;
                } elseif (in_array($clean, ['paid', 'amount_paid', 'amount', 'actual', 'actual_paid', 'paid_amount', 'fee_paid'], true)) {
                    $paidCol = $idx;
                    $hasHeader = true;
                }
            }

            if (!$hasHeader) {
                rewind($handle);
            }
        }

        $rowNumber = 0;
        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
            $rowNumber++;
            if (empty($data) || (count($data) === 1 && trim((string) $data[0]) === '')) {
                continue;
            }

            if (isset($data[0])) {
                $data[0] = preg_replace('/\x{EF}\x{BB}\x{BF}/', '', (string) $data[0]);
            }

            $rawSn = trim((string) ($data[$snCol] ?? $rowNumber));
            $rawName = trim((string) ($data[$nameCol] ?? ''));
            $rawPaid = trim((string) ($data[$paidCol] ?? ''));

            if ($rawName === '' && !empty($data[0]) && !is_numeric($data[0])) {
                $rawName = trim((string) $data[0]);
                $rawPaid = trim((string) ($data[1] ?? ''));
            }

            if (in_array(strtolower($rawName), ['name', 'student_name', 'fullname', 'full name', 'student'], true)) {
                continue;
            }

            // If the row or student name is blank, ignore it
            if ($rawName === '') {
                continue;
            }

            // If the paid value is blank, empty, '0', or <= 0, ignore the student (they didn't pay any money)
            $trimmedPaid = trim($rawPaid);
            $cleanPaid = (float) preg_replace('/[^0-9.]/', '', $trimmedPaid);
            if ($trimmedPaid === '' || in_array(strtolower($trimmedPaid), ['0', '0.0', '0.00', '-', 'nil', 'null', 'none', 'n/a'], true) || $cleanPaid <= 0.0001) {
                $ignoredCount++;
                continue;
            }

            $totalProcessed++;

            $studentData = find_student_by_full_name($rawName);
            $student = null;
            if ($studentData && !empty($studentData['id'])) {
                $student = Student::find($studentData['id']);
            }

            if (!$student) {
                $student = Student::where('admission_no', $rawName)
                    ->orWhere('student_number', $rawName)
                    ->orWhere('email', $rawName)
                    ->orWhere(DB::raw("LOWER(CONCAT(last_name, ' ', first_name))"), strtolower($rawName))
                    ->orWhere(DB::raw("LOWER(CONCAT(first_name, ' ', last_name))"), strtolower($rawName))
                    ->first();
            }

            if (!$student) {
                $failedRows[] = [
                    'row' => $rowNumber,
                    'sn' => $rawSn,
                    'name' => $rawName,
                    'paid' => $cleanPaid,
                    'reason' => "Student not found in database. Please verify name spelling or register student first.",
                ];
                continue;
            }

            try {
                DB::beginTransaction();

                $isSpecificStructure = str_starts_with($targetFeeType, 'structure:') || (is_numeric($targetFeeType) && (int)$targetFeeType > 0);
                $specificStructureId = $isSpecificStructure ? (int) str_replace('structure:', '', $targetFeeType) : null;
                $studentFee = null;

                if ($specificStructureId) {
                    $studentFee = StudentFee::where('student_id', $student->id)
                        ->where('fee_structure_id', $specificStructureId)
                        ->where(function($q) use ($sessionId) {
                            if ($sessionId) {
                                $q->where('session_id', $sessionId)->orWhereNull('session_id');
                            }
                        })
                        ->orderByDesc('id')
                        ->first();

                    if (!$studentFee) {
                        $studentFee = FeeAllocationService::allocateSpecificFeeForStudent($student, $specificStructureId, $sessionId, $termId);
                    }
                } else {
                    // Match by fee_type in student's allocated fees for this session/term
                    $studentFee = StudentFee::where('student_id', $student->id)
                        ->where(function($q) use ($sessionId) {
                            if ($sessionId) {
                                $q->where('session_id', $sessionId)->orWhereNull('session_id');
                            }
                        })
                        ->where(function($q) use ($termId) {
                            if ($termId) {
                                $q->where('term_id', $termId)->orWhereNull('term_id');
                            }
                        })
                        ->whereHas('feeStructure', function($q) use ($targetFeeType) {
                            $q->where('fee_type', $targetFeeType);
                        })
                        ->orderByDesc('id')
                        ->first();

                    if (!$studentFee) {
                        // Check if any student fee exists for this fee_type regardless of session/term
                        $studentFee = StudentFee::where('student_id', $student->id)
                            ->whereHas('feeStructure', function($q) use ($targetFeeType) {
                                $q->where('fee_type', $targetFeeType);
                            })
                            ->orderByDesc('id')
                            ->first();
                    }

                    if (!$studentFee) {
                        // Find matching fee structure by class, category, gender
                        $category = in_array(strtoupper((string) $student->category), ['NI', 'NEW_INTAKE', 'NEW']) ? 'NI' : 'OS';
                        $gender = strtoupper(substr((string) $student->gender, 0, 1));
                        if (!in_array($gender, ['M', 'F'])) $gender = 'M';

                        $candidates = FeeStructure::where('fee_type', $targetFeeType)
                            ->where('is_active', true)
                            ->where(function($q) use ($sessionId) {
                                if ($sessionId) {
                                    $q->where('session_id', $sessionId)->orWhereNull('session_id');
                                }
                            })
                            ->get();

                        $matchedFs = null;
                        if ($candidates->isNotEmpty()) {
                            $matchedFs = $candidates->sortByDesc(function($s) use ($student, $gender, $category, $termId) {
                                $score = 0;
                                if ($student->current_class_id && $s->class_id == $student->current_class_id) $score += 20;
                                if ($s->category === $category) $score += 10;
                                if ($s->gender === $gender || $s->gender === 'All') $score += 5;
                                if ($termId && $s->term_id == $termId) $score += 5;
                                return $score;
                            })->first();
                        }

                        if (!$matchedFs) {
                            $matchedFs = FeeStructure::where('fee_type', $targetFeeType)->where('is_active', true)->first();
                        }

                        if ($matchedFs) {
                            $studentFee = FeeAllocationService::allocateSpecificFeeForStudent($student, $matchedFs->id, $sessionId, $termId);
                        } elseif ($targetFeeType === 'tuition') {
                            $studentFee = FeeAllocationService::allocateFeeForStudent($student, $sessionId, $termId);
                        }
                    }
                }

                if (!$studentFee) {
                    $typeLabel = ucwords(str_replace('_', ' ', $targetFeeType));
                    $fs = FeeStructure::where('fee_type', $targetFeeType)->first();
                    if (!$fs && in_array($targetFeeType, schema_enum_values('fee_structures', 'fee_type'), true)) {
                        $fs = FeeStructure::create([
                            'name' => $typeLabel . ' Fee',
                            'description' => "Standard {$typeLabel} Fee",
                            'fee_type' => $targetFeeType,
                            'amount' => 59500.00,
                            'frequency' => 'annual',
                            'effective_from' => now()->toDateString(),
                            'session_id' => $sessionId,
                            'term_id' => $termId,
                            'category' => in_array(strtoupper((string) $student->category), ['NI', 'NEW_INTAKE', 'NEW']) ? 'NI' : 'OS',
                            'gender' => 'All',
                            'is_mandatory' => true,
                            'is_active' => true,
                        ]);
                    }
                    if ($fs) {
                        $studentFee = FeeAllocationService::allocateSpecificFeeForStudent($student, $fs->id, $sessionId, $termId);
                    }
                }

                if (!$studentFee) {
                    $typeLabel = ucwords(str_replace('_', ' ', $targetFeeType));
                    $baseAmt = 59500.00;
                    $studentFee = StudentFee::create([
                        'student_id' => $student->id,
                        'session_id' => $sessionId,
                        'term_id' => $termId,
                        'fee_structure_id' => null,
                        'base_amount' => $baseAmt,
                        'additional_amount' => 0.00,
                        'total_payable' => $baseAmt,
                        'amount_due' => $baseAmt,
                        'amount_paid' => 0.00,
                        'amount_owed' => $baseAmt,
                        'balance' => $baseAmt,
                        'due_date' => now()->addDays(30),
                        'status' => 'unpaid',
                        'notes' => "Auto-allocated {$typeLabel} fee for balance import",
                    ]);
                }

                if ((float)$studentFee->base_amount <= 0.001) {
                    if ($studentFee->feeStructure && (float)$studentFee->feeStructure->amount > 0) {
                        $studentFee->base_amount = (float)$studentFee->feeStructure->amount;
                        $studentFee->total_payable = (float)$studentFee->base_amount + (float)$studentFee->additional_amount;
                        $studentFee->save();
                    }
                }

                $allocatedBefore = (float) $studentFee->total_payable;
                $feeDisplayName = $studentFee->feeStructure->name ?? (ucwords(str_replace('_', ' ', $targetFeeType)) . ' Fee');

                $payRef = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                while (Payment::where('payment_reference', $payRef)->exists()) {
                    $payRef = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                }

                $studentName = trim($student->first_name . ' ' . ($student->other_names ?? '') . ' ' . $student->last_name);

                $payment = Payment::create([
                    'payment_reference' => $payRef,
                    'payable_type' => 'App\\Models\\StudentFee',
                    'payable_id' => $studentFee->id,
                    'amount' => $cleanPaid,
                    'payment_method' => $paymentMethod,
                    'payment_date' => $paymentDate,
                    'payer_name' => $studentName,
                    'payer_phone' => $student->parent_phone ?? $student->phone ?? null,
                    'description' => "Actual balance payment import - Fee deduction for " . $feeDisplayName,
                    'status' => 'completed',
                ]);

                // Recalculate totals and deduct from allocated fee
                $studentFee->recalculateTotals();

                $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                while (Receipt::where('receipt_number', $receiptNumber)->exists()) {
                    $receiptNumber = 'REC-' . date('Ymd') . '-' . strtoupper(Str::random(6));
                }

                $issuedBy = Auth::check() ? Auth::user()->name : ($_SESSION['username'] ?? 'Administrator');

                $receipt = Receipt::create([
                    'receipt_number' => $receiptNumber,
                    'payment_id' => $payment->id,
                    'student_id' => $student->id,
                    'amount_paid' => $cleanPaid,
                    'issued_date' => $paymentDate,
                    'issued_by' => $issuedBy,
                    'notes' => "Fee: {$feeDisplayName} | Allocated Fee: ₦" . number_format($allocatedBefore, 2) . " | Deducted Paid: ₦" . number_format($cleanPaid, 2) . " | Remaining Balance: ₦" . number_format($studentFee->balance, 2),
                ]);

                FinancialAuditLog::logAction('imported_actual_balance', $payment, [], [
                    'student_id' => $student->id,
                    'student_fee_id' => $studentFee->id,
                    'amount_paid' => $cleanPaid,
                    'allocated_fee' => $allocatedBefore,
                    'remaining_balance' => $studentFee->balance,
                    'receipt_number' => $receiptNumber,
                ]);

                DB::commit();

                $successCount++;
                $totalPaidAmount += $cleanPaid;

                $importedRows[] = [
                    'sn' => $rawSn,
                    'student_name' => $studentName,
                    'admission_no' => $student->admission_no ?? $student->student_number ?? 'N/A',
                    'class_name' => $student->schoolClass ? ($student->schoolClass->class_name . ' ' . ($student->schoolClass->section ?? '')) : 'N/A',
                    'fee_name' => $feeDisplayName,
                    'allocated_fee' => $allocatedBefore,
                    'paid_amount' => $cleanPaid,
                    'balance_remaining' => (float) $studentFee->balance,
                    'status' => $studentFee->status,
                    'payment_reference' => $payRef,
                    'receipt_number' => $receiptNumber,
                    'receipt_url' => url('/admin/fees/receipt.php?receipt=' . $receiptNumber),
                ];

            } catch (\Throwable $e) {
                DB::rollBack();
                $failedRows[] = [
                    'row' => $rowNumber,
                    'sn' => $rawSn,
                    'name' => $rawName,
                    'paid' => $cleanPaid,
                    'reason' => 'Database error: ' . $e->getMessage(),
                ];
            }
        }
        fclose($handle);

        $summary = [
            'total' => $totalProcessed,
            'success' => $successCount,
            'ignored' => $ignoredCount,
            'total_amount' => $totalPaidAmount,
            'failed' => count($failedRows),
            'records' => $importedRows,
            'failed_records' => $failedRows,
        ];

        session(['balance_import_summary' => $summary]);

        $displayFeeLabel = $isSpecificStructure
            ? ($studentFee && $studentFee->feeStructure ? $studentFee->feeStructure->name : 'Selected Fee Structure')
            : (ucwords(str_replace('_', ' ', $targetFeeType)) . ' Fee');
        $successMsg = "Imported {$successCount} actual balance payment(s) for [{$displayFeeLabel}] successfully totaling ₦" . number_format($totalPaidAmount, 2) . " with official receipts generated.";
        if ($ignoredCount > 0) {
            $successMsg .= " ({$ignoredCount} student(s) with ₦0 or blank were ignored as no payment was made).";
        }

        return redirect()->back()->with('balance_import_success', $successMsg)->with('balance_import_summary', $summary);
    }

    public function downloadSamplePaymentCsv()
    {
        return response()->streamDownload(function() {
            echo "sn,name,paid\n";
            echo "1,Adeyemi Babatunde John,40000\n";
            echo "2,Okonkwo Chinedu,59500\n";
            echo "3,Bello Mohammed Aliyu,25000\n";
        }, 'sample_student_payments.csv');
    }

    public function exportStudents()
    {
        $students = Student::with(['currentClass', 'currentSession'])->get();

        return response()->streamDownload(function() use ($students) {
            echo "Student Number,Admission No,First Name,Last Name,Gender,Category,Class,Session,Email\n";
            foreach($students as $student) {
                $class = $student->currentClass ? $student->currentClass->class_name : 'N/A';
                $session = $student->currentSession ? $student->currentSession->session_name : 'N/A';
                echo "\"{$student->student_number}\",\"{$student->admission_no}\",\"{$student->first_name}\",\"{$student->last_name}\",\"{$student->gender}\",\"{$student->category}\",\"{$class}\",\"{$session}\",\"{$student->email}\"\n";
            }
        }, 'students.csv');
    }

    // Sessions & Terms
    public function sessions()
    {
        $sessions = AcademicSession::all();
        return view('admin.sessions.index', compact('sessions'));
    }

    public function createSession()
    {
        return view('admin.sessions.create');
    }

    public function storeSession(Request $request)
    {
        $request->validate([
            'session_name' => 'required|string|unique:academic_sessions,session_name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'boolean',
        ]);

        AcademicSession::create($request->all());

        return redirect()->route('admin.sessions')->with('success', 'Session created successfully.');
    }

    public function terms()
    {
        $terms = Term::all();
        return view('admin.terms.index', compact('terms'));
    }

    public function createTerm()
    {
        return view('admin.terms.create');
    }

    public function storeTerm(Request $request)
    {
        $request->validate([
            'term_name' => 'required|string|unique:terms,term_name',
            'term_number' => 'required|integer|min:1',
        ]);

        Term::create($request->all());

        return redirect()->route('admin.terms')->with('success', 'Term created successfully.');
    }

    // Classes & Subjects & Staff
    public function classes()
    {
        $classes = SchoolClass::all();
        return view('admin.classes.index', compact('classes'));
    }

    public function createClass()
    {
        $teacherRole = Role::where('role_name', 'Teacher')->first();
        $teachers = $teacherRole ? User::where('role_id', $teacherRole->id)->get() : collect();
        return view('admin.classes.create', compact('teachers'));
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
            'grade_level' => 'required|string|max:255',
            'section' => 'nullable|string|max:50',
            'class_teacher_id' => 'nullable|exists:users,id',
            'max_capacity' => 'required|integer|min:1',
            'classroom_location' => 'nullable|string|max:255',
            'academic_year' => 'required|string|max:20',
            'status' => 'required|in:active,inactive,archived',
            'description' => 'nullable|string',
        ]);

        SchoolClass::create($request->all());

        return redirect()->route('admin.classes')->with('success', 'Class created successfully.');
    }

    public function subjects()
    {
        $subjects = Subject::all();
        return view('admin.subjects.index', compact('subjects'));
    }

    public function createSubject()
    {
        return view('admin.subjects.create');
    }

    public function storeSubject(Request $request)
    {
        $request->validate([
            'subject_code' => 'required|string|unique:subjects,subject_code',
            'subject_name' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'credit_hours' => 'required|integer|min:1',
            'subject_type' => 'required|in:core,elective,optional',
            'is_practical' => 'nullable|boolean',
            'status' => 'required|in:active,inactive,retired',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_practical'] = $request->has('is_practical');
        Subject::create($data);

        return redirect()->route('admin.subjects')->with('success', 'Subject created successfully.');
    }

    public function staff()
    {
        $staff = User::with('role')->where('role_id', '!=', Role::where('role_name', 'Student')->first()->id ?? 4)->paginate(20);
        return view('admin.staff.index', compact('staff'));
    }

    public function createStaff()
    {
        $roles = Role::where('role_name', '!=', 'Student')->get();
        return view('admin.staff.create', compact('roles'));
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return redirect()->route('admin.staff')->with('success', 'Staff created successfully.');
    }

    // Settings & Announcements
    public function settings()
    {
        $settings = SchoolSetting::first();
        if (!$settings) {
            $settings = SchoolSetting::create([
                'school_name' => 'Folu School Management System',
                'currency' => 'NGN',
                'timezone' => 'Africa/Lagos',
            ]);
        }
        $sessions = AcademicSession::all();
        $terms = Term::all();
        $activeSession = AcademicSession::where('is_active', true)->first();
        $activeTerm = Term::where('is_active', true)->first();
        return view('admin.settings.index', compact('settings', 'sessions', 'terms', 'activeSession', 'activeTerm'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_email' => 'nullable|email|max:255',
            'school_phone' => 'nullable|string|max:50',
            'school_motto' => 'nullable|string|max:255',
            'school_address' => 'nullable|string',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'active_session_id' => 'nullable|exists:academic_sessions,id',
            'active_term_id' => 'nullable|exists:terms,id',
        ]);

        $settings = SchoolSetting::first();
        if (!$settings) {
            $settings = new SchoolSetting();
        }

        $settings->school_name = $request->school_name;
        $settings->school_email = $request->school_email;
        $settings->school_phone = $request->school_phone;
        $settings->school_motto = $request->school_motto;
        $settings->school_address = $request->school_address;
        $settings->currency = $request->currency;
        $settings->timezone = $request->timezone;

        if ($request->hasFile('school_logo')) {
            $path = $request->file('school_logo')->store('school', 'public');
            $settings->school_logo = $path;
        }

        $additional = $settings->additional_settings ?? [];
        $additional['principal_name'] = $request->input('principal_name');
        $additional['admissions_open'] = $request->has('admissions_open');
        $additional['portal_notice'] = $request->input('portal_notice');
        $settings->additional_settings = $additional;

        $settings->save();

        if ($request->filled('active_session_id')) {
            AcademicSession::where('is_active', true)->update(['is_active' => false]);
            AcademicSession::where('id', $request->active_session_id)->update(['is_active' => true]);
        }

        if ($request->filled('active_term_id')) {
            Term::where('is_active', true)->update(['is_active' => false]);
            Term::where('id', $request->active_term_id)->update(['is_active' => true]);
        }

        FinancialAuditLog::logAction('updated_school_settings', null, [], $settings->toArray());

        return redirect()->route('admin.settings')->with('success', 'School details and site preferences updated successfully.');
    }

    public function announcements()
    {
        $announcements = Announcement::latest()->paginate(10);
        $sessions = AcademicSession::all();
        return view('admin.announcements.index', compact('announcements', 'sessions'));
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'body' => 'required|string',
            'audience' => 'required|in:all,students,teachers,admins',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
        ]);
        $data['author_id'] = auth()->id() ?? 1;
        $data['is_published'] = true;
        $data['published_at'] = now();
        Announcement::create($data);
        return redirect()->route('admin.announcements')->with('success', 'Announcement published.');
    }

    public function timetable()
    {
        return view('admin.classes.timetable');
    }

    public function assignments()
    {
        return view('admin.classes.assignments');
    }

    // Profile Management
    public function profile()
    {
        $user = auth()->user();
        if (!$user && isset($_SESSION['adid'])) {
            $user = User::find($_SESSION['adid']);
        }
        return view('admin.profile.index', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user() ?? User::find($_SESSION['adid'] ?? 0);
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'phone')) {
            $user->phone = $request->phone;
        }
        $user->save();

        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['username'] = $user->username ?? $user->name;
            $_SESSION['email'] = $user->email;
        }

        FinancialAuditLog::logAction('updated_admin_profile', $user, [], $user->toArray());

        return back()->with('success', 'Admin profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user() ?? User::find($_SESSION['adid'] ?? 0);
        if (!$user) {
            return redirect()->route('login');
        }

        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $user->password) && !password_verify($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        FinancialAuditLog::logAction('changed_admin_password', $user, [], []);

        return back()->with('success', 'Password updated successfully.');
    }

    // Staff details AJAX endpoint
    public function getStaffDetails(Staff $staff)
    {
        $staff->load(['assignedClass', 'assignedSubject']);
        return response()->json([
            'id' => $staff->id,
            'full_name' => $staff->full_name,
            'staff_type' => $staff->staff_type ?? 'full_time',
            'staff_type_label' => $staff->formatted_staff_type,
            'class_or_subject' => $staff->class_or_subject,
            'salary' => (float) $staff->salary,
            'position' => $staff->position,
            'department' => $staff->department,
        ]);
    }

    // Payroll Management
    public function payroll(Request $request)
    {
        $query = Payroll::with(['staff.assignedClass', 'staff.assignedSubject'])->latest('pay_date');

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('staff_type')) {
            $query->where('staff_type', $request->staff_type);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('staff', function($sq) use ($search) {
                    $sq->where('first_name', 'LIKE', "%{$search}%")
                      ->orWhere('last_name', 'LIKE', "%{$search}%")
                      ->orWhere('staff_number', 'LIKE', "%{$search}%");
                })->orWhere('class_subject', 'LIKE', "%{$search}%")
                  ->orWhere('payment_reference', 'LIKE', "%{$search}%")
                  ->orWhere('month', 'LIKE', "%{$search}%");
            });
        }

        $payrolls = $query->paginate(20)->withQueryString();

        $staffList = Staff::where('status', 'active')->orderBy('first_name')->get();
        if ($staffList->isEmpty()) {
            $staffList = Staff::orderBy('first_name')->get();
        }

        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $years = range((int) date('Y') - 2, (int) date('Y') + 2);

        $stats = [
            'total_payroll_paid' => Payroll::where('status', 'paid')->sum('net_pay'),
            'total_payroll_pending' => Payroll::whereIn('status', ['pending', 'not_paid', 'unpaid'])->sum('net_pay'),
            'total_records' => Payroll::count(),
            'paid_records_count' => Payroll::where('status', 'paid')->count(),
        ];

        $schoolSettings = SchoolSetting::first();

        return view('admin.payroll.index', compact('payrolls', 'staffList', 'stats', 'schoolSettings', 'months', 'years'));
    }

    public function createPayroll()
    {
        $staffList = Staff::orderBy('first_name')->get();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $years = range((int) date('Y') - 2, (int) date('Y') + 2);
        $schoolSettings = SchoolSetting::first();
        return view('admin.payroll.create', compact('staffList', 'classes', 'subjects', 'schoolSettings', 'months', 'years'));
    }

    public function storePayroll(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'staff_type' => 'required|in:full_time,part_time',
            'class_subject' => 'nullable|string|max:255',
            'month' => 'required|string',
            'year' => 'required|integer',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'overtime_pay' => 'nullable|numeric|min:0',
            'pay_date' => 'required|date',
            'status' => 'required|in:paid,not_paid,pending,processed',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'update_staff_profile' => 'nullable|boolean',
        ]);

        $staff = Staff::findOrFail($request->staff_id);

        $basicSalary = (float) $request->basic_salary;
        $allowances = (float) ($request->allowances ?? 0);
        $deductions = (float) ($request->deductions ?? 0);
        $bonuses = (float) ($request->bonuses ?? 0);
        $overtimePay = (float) ($request->overtime_pay ?? 0);

        $grossPay = $basicSalary + $allowances + $bonuses + $overtimePay;
        $netPay = max(0, $grossPay - $deductions);

        $classSubject = $request->class_subject ?: $staff->class_or_subject;

        $reference = $request->payment_reference ?: ('PAY-' . date('Ym') . '-' . str_pad(Payroll::count() + 1, 4, '0', STR_PAD_LEFT));

        $payroll = Payroll::create([
            'staff_id' => $staff->id,
            'staff_type' => $request->staff_type,
            'class_subject' => $classSubject,
            'month' => $request->month,
            'year' => $request->year,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'bonuses' => $bonuses,
            'overtime_pay' => $overtimePay,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'pay_period_start' => $request->pay_date,
            'pay_period_end' => $request->pay_date,
            'pay_date' => $request->pay_date,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'payment_reference' => $reference,
            'remarks' => $request->remarks,
        ]);

        if ($request->has('update_staff_profile')) {
            $staff->update([
                'staff_type' => $request->staff_type,
                'salary' => $basicSalary,
                'class_or_subject_custom' => $request->class_subject,
            ]);
        }

        FinancialAuditLog::logAction('recorded_payroll_payment', $payroll, [], $payroll->toArray());

        return redirect()->route('admin.payroll')->with('success', "Payroll payment for {$staff->full_name} ({$request->month} {$request->year}) recorded successfully.");
    }

    public function showPayroll(Payroll $payroll)
    {
        $payroll->load(['staff.assignedClass', 'staff.assignedSubject']);
        $schoolSettings = SchoolSetting::first();
        return view('admin.payroll.show', compact('payroll', 'schoolSettings'));
    }

    public function editPayroll(Payroll $payroll)
    {
        $payroll->load('staff');
        $staffList = Staff::orderBy('first_name')->get();
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        $years = range((int) date('Y') - 2, (int) date('Y') + 2);
        $schoolSettings = SchoolSetting::first();
        return view('admin.payroll.edit', compact('payroll', 'staffList', 'schoolSettings', 'months', 'years'));
    }

    public function updatePayroll(Request $request, Payroll $payroll)
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'staff_type' => 'required|in:full_time,part_time',
            'class_subject' => 'nullable|string|max:255',
            'month' => 'required|string',
            'year' => 'required|integer',
            'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'bonuses' => 'nullable|numeric|min:0',
            'pay_date' => 'required|date',
            'status' => 'required|in:paid,not_paid,pending,processed',
            'payment_method' => 'required|string',
            'payment_reference' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ]);

        $basicSalary = (float) $request->basic_salary;
        $allowances = (float) ($request->allowances ?? 0);
        $deductions = (float) ($request->deductions ?? 0);
        $bonuses = (float) ($request->bonuses ?? 0);
        $overtimePay = (float) ($payroll->overtime_pay ?? 0);

        $grossPay = $basicSalary + $allowances + $bonuses + $overtimePay;
        $netPay = max(0, $grossPay - $deductions);

        $oldData = $payroll->toArray();

        $payroll->update([
            'staff_id' => $request->staff_id,
            'staff_type' => $request->staff_type,
            'class_subject' => $request->class_subject,
            'month' => $request->month,
            'year' => $request->year,
            'basic_salary' => $basicSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'bonuses' => $bonuses,
            'gross_pay' => $grossPay,
            'net_pay' => $netPay,
            'pay_date' => $request->pay_date,
            'status' => $request->status,
            'payment_method' => $request->payment_method,
            'payment_reference' => $request->payment_reference ?: $payroll->payment_reference,
            'remarks' => $request->remarks,
        ]);

        FinancialAuditLog::logAction('updated_payroll_payment', $payroll, $oldData, $payroll->toArray());

        return redirect()->route('admin.payroll')->with('success', 'Payroll record updated successfully.');
    }

    public function destroyPayroll(Payroll $payroll)
    {
        $oldData = $payroll->toArray();
        $payroll->delete();

        FinancialAuditLog::logAction('deleted_payroll_payment', null, $oldData, []);

        return redirect()->route('admin.payroll')->with('success', 'Payroll record deleted successfully.');
    }

    public function payrollReceipt(Payroll $payroll)
    {
        $payroll->load(['staff.assignedClass', 'staff.assignedSubject']);
        $schoolSettings = SchoolSetting::first();
        return view('admin.payroll.receipt', compact('payroll', 'schoolSettings'));
    }

    public function exportPayroll(Request $request)
    {
        $payrolls = Payroll::with('staff')->latest('pay_date')->get();

        return response()->streamDownload(function() use ($payrolls) {
            echo "Reference,Staff Name,Staff Type,Class / Subject,Month Paid For,Basic Salary,Net Pay,Payment Date,Payment Method,Status,Remarks\n";
            foreach ($payrolls as $p) {
                $staffName = $p->staff ? $p->staff->full_name : 'N/A';
                $staffType = $p->formatted_staff_type;
                $classSub = $p->class_subject ?: ($p->staff ? $p->staff->class_or_subject : 'N/A');
                $monthYear = $p->pay_period_formatted;
                $payDate = $p->pay_date ? $p->pay_date->format('Y-m-d') : '';
                $status = $p->formatted_status;
                echo "\"{$p->payment_reference}\",\"{$staffName}\",\"{$staffType}\",\"{$classSub}\",\"{$monthYear}\",\"{$p->basic_salary}\",\"{$p->net_pay}\",\"{$payDate}\",\"{$p->payment_method}\",\"{$status}\",\"{$p->remarks}\"\n";
            }
        }, 'payroll_report_' . date('Y_m_d') . '.csv');
    }

    // Expenditure Management
    public function expenditures(Request $request)
    {
        $query = Expenditure::with('recorder')->latest('expenditure_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('expenditure_number', 'LIKE', "%{$search}%")
                  ->orWhere('vendor_recipient', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('from_date')) {
            $query->where('expenditure_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('expenditure_date', '<=', $request->to_date);
        }

        $expenditures = $query->paginate(20)->withQueryString();

        $categories = [
            'Utilities',
            'Maintenance & Repairs',
            'Supplies & Stationery',
            'Equipment & IT',
            'Events & Activities',
            'Salaries & Wages',
            'Transport & Logistics',
            'Miscellaneous',
        ];

        $stats = [
            'total_amount' => Expenditure::where('status', '!=', 'cancelled')->sum('amount'),
            'paid_amount' => Expenditure::where('status', 'paid')->sum('amount'),
            'pending_amount' => Expenditure::where('status', 'pending')->sum('amount'),
            'total_count' => Expenditure::count(),
        ];

        $schoolSettings = SchoolSetting::first();

        return view('admin.expenditures.index', compact('expenditures', 'categories', 'stats', 'schoolSettings'));
    }

    public function createExpenditure()
    {
        $categories = [
            'Utilities',
            'Maintenance & Repairs',
            'Supplies & Stationery',
            'Equipment & IT',
            'Events & Activities',
            'Salaries & Wages',
            'Transport & Logistics',
            'Miscellaneous',
        ];
        $schoolSettings = SchoolSetting::first();
        return view('admin.expenditures.create', compact('categories', 'schoolSettings'));
    }

    public function storeExpenditure(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expenditure_date' => 'required|date',
            'vendor_recipient' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'status' => 'required|in:paid,pending,approved,cancelled',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('expenditures', 'public');
        }

        $expNumber = 'EXP-' . date('Ym') . '-' . str_pad(Expenditure::count() + 1, 4, '0', STR_PAD_LEFT);

        $expenditure = Expenditure::create([
            'expenditure_number' => $expNumber,
            'title' => $request->title,
            'category' => $request->category,
            'amount' => $request->amount,
            'expenditure_date' => $request->expenditure_date,
            'vendor_recipient' => $request->vendor_recipient,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'description' => $request->description,
            'receipt_path' => $receiptPath,
            'recorded_by' => auth()->id() ?? ($_SESSION['adid'] ?? null),
        ]);

        FinancialAuditLog::logAction('created_expenditure', $expenditure, [], $expenditure->toArray());

        return redirect()->route('admin.expenditures')->with('success', 'Expenditure recorded successfully.');
    }

    public function showExpenditure(Expenditure $expenditure)
    {
        $expenditure->load('recorder');
        $schoolSettings = SchoolSetting::first();
        return view('admin.expenditures.show', compact('expenditure', 'schoolSettings'));
    }

    public function editExpenditure(Expenditure $expenditure)
    {
        $categories = [
            'Utilities',
            'Maintenance & Repairs',
            'Supplies & Stationery',
            'Equipment & IT',
            'Events & Activities',
            'Salaries & Wages',
            'Transport & Logistics',
            'Miscellaneous',
        ];
        $schoolSettings = SchoolSetting::first();
        return view('admin.expenditures.edit', compact('expenditure', 'categories', 'schoolSettings'));
    }

    public function updateExpenditure(Request $request, Expenditure $expenditure)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'expenditure_date' => 'required|date',
            'vendor_recipient' => 'nullable|string|max:255',
            'payment_method' => 'required|string',
            'status' => 'required|in:paid,pending,approved,cancelled',
            'description' => 'nullable|string',
            'receipt' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->only([
            'title', 'category', 'amount', 'expenditure_date',
            'vendor_recipient', 'payment_method', 'status', 'description'
        ]);

        if ($request->hasFile('receipt')) {
            if ($expenditure->receipt_path) {
                Storage::disk('public')->delete($expenditure->receipt_path);
            }
            $data['receipt_path'] = $request->file('receipt')->store('expenditures', 'public');
        }

        $oldData = $expenditure->toArray();
        $expenditure->update($data);

        FinancialAuditLog::logAction('updated_expenditure', $expenditure, $oldData, $expenditure->toArray());

        return redirect()->route('admin.expenditures')->with('success', 'Expenditure record updated successfully.');
    }

    public function destroyExpenditure(Expenditure $expenditure)
    {
        if ($expenditure->receipt_path) {
            Storage::disk('public')->delete($expenditure->receipt_path);
        }
        $oldData = $expenditure->toArray();
        $expenditure->delete();

        FinancialAuditLog::logAction('deleted_expenditure', null, $oldData, []);

        return redirect()->route('admin.expenditures')->with('success', 'Expenditure record deleted successfully.');
    }

    public function exportExpenditures(Request $request)
    {
        $expenditures = Expenditure::latest('expenditure_date')->get();

        return response()->streamDownload(function() use ($expenditures) {
            echo "Expenditure No,Title,Category,Amount,Date,Vendor/Recipient,Payment Method,Status,Description\n";
            foreach ($expenditures as $e) {
                $date = $e->expenditure_date ? $e->expenditure_date->format('Y-m-d') : '';
                $status = $e->formatted_status;
                echo "\"{$e->expenditure_number}\",\"{$e->title}\",\"{$e->category}\",\"{$e->amount}\",\"{$date}\",\"{$e->vendor_recipient}\",\"{$e->payment_method}\",\"{$status}\",\"{$e->description}\"\n";
            }
        }, 'expenditures_report_' . date('Y_m_d') . '.csv');
    }
}
