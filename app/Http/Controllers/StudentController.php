<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Models\StudentFee;
use App\Models\Payment;
use App\Models\Complaint;
use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\AdditionalCharge;
use App\Services\FeeAllocationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        require_once base_path('db/config.php');
        require_once base_path('db/functions.php');
    }

    /**
     * Retrieve the authenticated student model safely.
     */
    protected function getAuthenticatedStudent(): ?Student
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $student = Student::with(['currentClass', 'currentSession', 'currentTerm'])
            ->where('email', $user->email)
            ->orWhere('admission_no', $user->username)
            ->orWhere('student_number', $user->username)
            ->orWhere('user_link', $user->id)
            ->first();

        if (!$student && !empty($_SESSION['student_id'])) {
            $student = Student::with(['currentClass', 'currentSession', 'currentTerm'])->find($_SESSION['student_id']);
        }

        return $student;
    }

    public function dashboard()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found. Please contact administration.');
        }

        // Active Session & Term
        $currentSession = AcademicSession::where('is_active', true)->first() ?: $student->currentSession;
        $currentTerm = Term::where('is_active', true)->first() ?: $student->currentTerm;

        // Auto-allocate fee if none exists
        $existingFee = StudentFee::where('student_id', $student->id)->latest('id')->first();
        if (!$existingFee) {
            FeeAllocationService::allocateFeeForStudent($student);
        }

        // Student fees with fee structure
        $studentFees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure', 'session', 'term'])
            ->orderBy('id', 'desc')
            ->get();

        // Additional charges
        $additionalCharges = AdditionalCharge::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->get();

        $allocatedDue = $studentFees->sum(fn($f) => (float)($f->base_amount > 0.001 ? $f->base_amount : $f->amount_due));
        $allocatedPaid = $studentFees->sum(fn($f) => (float)$f->amount_paid);
        $extraDue = $additionalCharges->sum(fn($c) => (float)$c->amount);
        $extraPaid = $additionalCharges->sum(fn($c) => (float)$c->amount_paid);

        $totalFees = $allocatedDue + $extraDue;
        $totalPaid = $allocatedPaid + $extraPaid;
        $pendingFees = max(0.00, $totalFees - $totalPaid);
        $feePaymentProgress = $totalFees > 0 ? min(100, round(($totalPaid / $totalFees) * 100)) : 100;
        $feeStatus = ($pendingFees <= 0.001) ? 'cleared' : ($totalPaid > 0 ? 'partial' : 'unpaid');

        // Recent Grades using helper for accurate schema support
        $recentResults = function_exists('get_student_results')
            ? array_slice(get_student_results($student->id, $currentSession?->id), 0, 6)
            : [];

        // Grade average
        $gradeAverage = function_exists('student_grade_average') 
            ? student_grade_average($student->id) 
            : 0;

        // Attendance stats & records
        $attendanceRecords = function_exists('get_student_attendance_history') 
            ? get_student_attendance_history($student->id, $currentSession?->id) 
            : [];
        $totalAttendanceDays = count($attendanceRecords);
        $daysPresent = 0;
        $daysAbsent = 0;
        $daysLate = 0;
        $daysExcused = 0;
        foreach ($attendanceRecords as $att) {
            $st = strtolower((string)($att['status'] ?? ''));
            if ($st === 'present') $daysPresent++;
            elseif ($st === 'absent') $daysAbsent++;
            elseif ($st === 'late') $daysLate++;
            elseif (in_array($st, ['excused', 'sick', 'holiday'], true)) $daysExcused++;
        }
        $attendancePercentage = $totalAttendanceDays > 0 ? round(($daysPresent / $totalAttendanceDays) * 100, 1) : 0;
        $recentAttendance = array_slice($attendanceRecords, 0, 5);

        // Enrolled / Allocated subjects
        $allocatedSubjects = function_exists('get_student_allocated_subjects') 
            ? get_student_allocated_subjects($student->id, $currentSession?->id) 
            : [];
        $totalSubjectsCount = count($allocatedSubjects);

        // Recent payments
        $studentFeeIds = $studentFees->pluck('id')->toArray();
        $addChargeIds = $additionalCharges->pluck('id')->toArray();
        $recentPayments = Payment::where(function($q) use ($studentFeeIds, $addChargeIds, $student) {
            if (!empty($studentFeeIds)) {
                $q->where(function($sub) use ($studentFeeIds) {
                    $sub->whereIn('payable_id', $studentFeeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'student_fee')
                                  ->orWhere('payable_type', 'App\\Models\\StudentFee')
                                  ->orWhere('payable_type', 'LIKE', '%StudentFee%');
                        });
                });
            }
            if (!empty($addChargeIds)) {
                $q->orWhere(function($sub) use ($addChargeIds) {
                    $sub->whereIn('payable_id', $addChargeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'additional_charge')
                                  ->orWhere('payable_type', 'App\\Models\\AdditionalCharge')
                                  ->orWhere('payable_type', 'LIKE', '%AdditionalCharge%');
                        });
                });
            }
            $q->orWhereHas('receipt', fn($r) => $r->where('student_id', $student->id));
        })->where('status', 'completed')
          ->orderBy('payment_date', 'desc')
          ->orderBy('id', 'desc')
          ->limit(5)
          ->get();

        // Announcements
        $announcements = function_exists('get_announcements_for_audience') 
            ? get_announcements_for_audience('students', $currentSession?->id) 
            : [];

        return view('student.dashboard', compact(
            'student', 'currentSession', 'currentTerm', 'studentFees', 'additionalCharges',
            'totalFees', 'totalPaid', 'pendingFees', 'feePaymentProgress', 'feeStatus',
            'recentResults', 'gradeAverage',
            'attendanceRecords', 'totalAttendanceDays', 'daysPresent', 'daysAbsent', 'daysLate', 'daysExcused',
            'attendancePercentage', 'recentAttendance',
            'allocatedSubjects', 'totalSubjectsCount',
            'recentPayments', 'announcements'
        ));
    }

    public function profile()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentSession = AcademicSession::where('is_active', true)->first() ?: $student->currentSession;
        $currentTerm = Term::where('is_active', true)->first() ?: $student->currentTerm;
        $allocatedSubjects = function_exists('get_student_allocated_subjects') 
            ? get_student_allocated_subjects($student->id, $currentSession?->id) 
            : [];

        $parent = null;
        try {
            if (Schema::hasTable('student_parents') && Schema::hasTable('parents')) {
                $parent = DB::table('student_parents')
                    ->join('parents', 'student_parents.parent_link', '=', 'parents.id')
                    ->where('student_parents.student_link', $student->id)
                    ->select('parents.*', 'student_parents.relationship', 'student_parents.is_primary_contact')
                    ->first();
            }
        } catch (\Throwable $e) {}

        $attendancePercentage = function_exists('student_attendance_percentage') 
            ? student_attendance_percentage($student->id) 
            : 0;
        $gradeAverage = function_exists('student_grade_average') 
            ? student_grade_average($student->id) 
            : 0;

        return view('student.profile', compact(
            'student', 'currentSession', 'currentTerm', 'allocatedSubjects', 'attendancePercentage', 'gradeAverage', 'parent'
        ));
    }

    public function results()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentSession = AcademicSession::where('is_active', true)->first() ?: $student->currentSession;
        $currentTerm = Term::where('is_active', true)->first() ?: $student->currentTerm;

        $results = function_exists('get_student_results')
            ? get_student_results($student->id, null, null)
            : [];

        $grades = StudentGrade::where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('id', 'desc')
            ->get();

        $gradeAverage = function_exists('student_grade_average') 
            ? student_grade_average($student->id) 
            : ($grades->count() > 0 ? $grades->avg('total_score') : 0);

        return view('student.results', compact('student', 'results', 'grades', 'gradeAverage', 'currentSession', 'currentTerm'));
    }

    public function payments()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentSession = AcademicSession::where('is_active', true)->first() ?: $student->currentSession;
        $currentTerm = Term::where('is_active', true)->first() ?: $student->currentTerm;

        $studentFees = StudentFee::where('student_id', $student->id)
            ->with(['feeStructure', 'session', 'term'])
            ->orderBy('id', 'desc')
            ->get();

        $additionalCharges = AdditionalCharge::where('student_id', $student->id)
            ->orderBy('id', 'desc')
            ->get();

        $studentFeeIds = $studentFees->pluck('id')->toArray();
        $addChargeIds = $additionalCharges->pluck('id')->toArray();

        $payments = Payment::where(function($q) use ($studentFeeIds, $addChargeIds, $student) {
            if (!empty($studentFeeIds)) {
                $q->where(function($sub) use ($studentFeeIds) {
                    $sub->whereIn('payable_id', $studentFeeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'student_fee')
                                  ->orWhere('payable_type', 'App\\Models\\StudentFee')
                                  ->orWhere('payable_type', 'LIKE', '%StudentFee%');
                        });
                });
            }
            if (!empty($addChargeIds)) {
                $q->orWhere(function($sub) use ($addChargeIds) {
                    $sub->whereIn('payable_id', $addChargeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'additional_charge')
                                  ->orWhere('payable_type', 'App\\Models\\AdditionalCharge')
                                  ->orWhere('payable_type', 'LIKE', '%AdditionalCharge%');
                        });
                });
            }
            $q->orWhereHas('receipt', fn($r) => $r->where('student_id', $student->id));
        })->where('status', 'completed')
          ->orderBy('payment_date', 'desc')
          ->orderBy('id', 'desc')
          ->get();

        $totalDue = $studentFees->sum(fn($f) => (float)($f->base_amount > 0.001 ? $f->base_amount : $f->amount_due)) + $additionalCharges->sum('amount');
        $totalPaid = $studentFees->sum('amount_paid') + $additionalCharges->sum('amount_paid');
        $totalBalance = max(0.00, $totalDue - $totalPaid);

        return view('student.payments', compact('student', 'studentFees', 'additionalCharges', 'payments', 'totalDue', 'totalPaid', 'totalBalance', 'currentSession', 'currentTerm'));
    }

    public function receipts()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $studentFees = StudentFee::where('student_id', $student->id)->get();
        $studentFeeIds = $studentFees->pluck('id')->toArray();
        $addChargeIds = AdditionalCharge::where('student_id', $student->id)->pluck('id')->toArray();

        $payments = Payment::where(function($q) use ($studentFeeIds, $addChargeIds, $student) {
            if (!empty($studentFeeIds)) {
                $q->where(function($sub) use ($studentFeeIds) {
                    $sub->whereIn('payable_id', $studentFeeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'student_fee')
                                  ->orWhere('payable_type', 'App\\Models\\StudentFee')
                                  ->orWhere('payable_type', 'LIKE', '%StudentFee%');
                        });
                });
            }
            if (!empty($addChargeIds)) {
                $q->orWhere(function($sub) use ($addChargeIds) {
                    $sub->whereIn('payable_id', $addChargeIds)
                        ->where(function($pType) {
                            $pType->where('payable_type', 'additional_charge')
                                  ->orWhere('payable_type', 'App\\Models\\AdditionalCharge')
                                  ->orWhere('payable_type', 'LIKE', '%AdditionalCharge%');
                        });
                });
            }
            $q->orWhereHas('receipt', fn($r) => $r->where('student_id', $student->id));
        })->where('status', 'completed')
          ->with(['receipt'])
          ->orderBy('payment_date', 'desc')
          ->orderBy('id', 'desc')
          ->get();

        return view('student.receipts', compact('student', 'payments'));
    }

    public function complaints()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $complaints = Complaint::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.complaints', compact('student', 'complaints'));
    }

    public function submitComplaint(Request $request)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'complaint_type' => 'required|string',
        ]);

        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        Complaint::create([
            'student_id' => $student->id,
            'complaint_type' => $request->complaint_type,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return redirect()->route('student.complaints')->with('success', 'Your inquiry/complaint has been submitted successfully.');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:4096',
        ]);

        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return response()->json(['error' => 'Student profile not found.'], 404);
        }

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $student->id . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $request->document_type) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('student_documents', $filename, 'public');

            try {
                if (Schema::hasTable('student_documents')) {
                    DB::table('student_documents')->insert([
                        'student_id' => $student->id,
                        'document_type' => $request->document_type,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_size' => $file->getSize(),
                        'file_mime_type' => $file->getClientMimeType(),
                        'notes' => $request->notes ?? null,
                        'upload_date' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {}

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'path' => $path
            ]);
        }

        return response()->json(['error' => 'No file was uploaded.'], 400);
    }

    public function documents()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $documents = collect();
        try {
            if (Schema::hasTable('student_documents')) {
                $documents = DB::table('student_documents')
                    ->where('student_id', $student->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        } catch (\Throwable $e) {}

        return view('student.documents', compact('student', 'documents'));
    }

    public function checkProgression()
    {
        $student = $this->getAuthenticatedStudent();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $currentSession = AcademicSession::where('is_active', true)->first() ?: $student->currentSession;
        $currentTerm = Term::where('is_active', true)->first() ?: $student->currentTerm;

        $results = function_exists('get_student_results')
            ? get_student_results($student->id, $currentSession?->id, null)
            : [];

        $grades = StudentGrade::where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('id', 'desc')
            ->get();

        $gradeAverage = function_exists('student_grade_average') 
            ? student_grade_average($student->id) 
            : ($grades->count() > 0 ? $grades->avg('total_score') : 0);

        $attendancePct = function_exists('student_attendance_percentage')
            ? student_attendance_percentage($student->id)
            : 0;

        $canProgress = $gradeAverage >= 50 && $attendancePct >= 60;

        return view('student.progression', compact('student', 'results', 'grades', 'gradeAverage', 'attendancePct', 'canProgress', 'currentSession', 'currentTerm'));
    }
}
