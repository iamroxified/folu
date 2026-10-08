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
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Announcement;
use App\Models\FinancialAuditLog;
use App\Services\FeeAllocationService;
use Illuminate\Http\Request;
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

        $data['student_number'] = 'STU' . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);
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
            'fee_type' => 'required|in:tuition,registration,transport,library,sports,laboratory,uniform,exam,other',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'category' => 'required|in:NI,OS',
            'gender' => 'required|in:M,F,All',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $category = $request->category;
        $termId = $category === 'NI' ? null : $request->term_id;

        if ($category === 'OS' && !$termId) {
            return back()->withInput()->withErrors(['term_id' => 'Term selection is required for Returning Student fee structures.']);
        }

        // CRITICAL BUSINESS RULE: Prevent duplicate fee structure for exact same combination
        $duplicateQuery = FeeStructure::where('session_id', $request->session_id)
            ->where('class_id', $request->class_id)
            ->where('gender', $request->gender)
            ->where('category', $category)
            ->where('is_active', true);

        if ($category === 'OS' && $termId) {
            $duplicateQuery->where('term_id', $termId);
        }

        if ($duplicateQuery->exists()) {
            return back()->withInput()->withErrors(['duplicate' => 'An active fee structure already exists for this exact combination (Session, Class, Gender, Student Type, Term).']);
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
            'fee_type' => 'required|in:tuition,registration,transport,library,sports,laboratory,uniform,exam,other',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'category' => 'required|in:NI,OS',
            'gender' => 'required|in:M,F,All',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'nullable|exists:terms,id',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $category = $request->category;
        $termId = $category === 'NI' ? null : $request->term_id;

        if ($category === 'OS' && !$termId) {
            return back()->withInput()->withErrors(['term_id' => 'Term selection is required for Returning Student fee structures.']);
        }

        $duplicateQuery = FeeStructure::where('session_id', $request->session_id)
            ->where('class_id', $request->class_id)
            ->where('gender', $request->gender)
            ->where('category', $category)
            ->where('is_active', true)
            ->where('id', '!=', $fee->id);

        if ($category === 'OS' && $termId) {
            $duplicateQuery->where('term_id', $termId);
        }

        if ($duplicateQuery->exists()) {
            return back()->withInput()->withErrors(['duplicate' => 'An active fee structure already exists for this exact combination (Session, Class, Gender, Student Type, Term).']);
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
            $studentTypeRaw = trim((string) ($data[6] ?? 'NI'));
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

            $categoryClean = in_array(strtoupper($studentTypeRaw), ['RETURNING', 'OS', 'OLD']) ? 'OS' : 'NI';

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
                $admissionNo = !empty($studentId) ? $studentId : generate_student_admission_no();
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
                    'user_id' => $user->id,
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
