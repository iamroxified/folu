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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        // Get current session and term
        $currentSession = AcademicSession::where('is_active', true)->first();
        $currentTerm = Term::where('is_active', true)->first();

        // Get student's fees
        $studentFees = StudentFee::where('student_id', $student->id)->get();
        $totalFees = $studentFees->sum('amount_due');
        $totalPaid = $studentFees->sum('amount_paid');
        $pendingFees = $totalFees - $totalPaid;

        // Get recent grades
        $recentGrades = StudentGrade::where('student_id', $student->id)
            ->with(['subject'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('student.dashboard', compact(
            'student', 'currentSession', 'currentTerm', 'studentFees',
            'totalFees', 'totalPaid', 'pendingFees', 'recentGrades'
        ));
    }

    public function profile()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        return view('student.profile', compact('student'));
    }

    public function results()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $grades = StudentGrade::where('student_id', $student->id)
            ->with(['subject', 'academicSession', 'term'])
            ->orderBy('academic_session_id', 'desc')
            ->orderBy('term_id', 'desc')
            ->orderBy('subject_id')
            ->get()
            ->groupBy(['academic_session_id', 'term_id']);

        return view('student.results', compact('student', 'grades'));
    }

    public function payments()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $studentFees = StudentFee::where('student_id', $student->id)
            ->with('feeStructure')
            ->get();

        $payments = Payment::whereHas('payable', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })->with('payable.feeStructure')
          ->orderBy('payment_date', 'desc')
          ->get();

        return view('student.payments', compact('student', 'studentFees', 'payments'));
    }

    public function receipts()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        $payments = Payment::whereHas('payable', function($query) use ($student) {
            $query->where('student_id', $student->id);
        })->with('payable.feeStructure')
          ->orderBy('payment_date', 'desc')
          ->get();

        return view('student.receipts', compact('student', 'payments'));
    }

    public function complaints()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

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

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

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

        return redirect()->route('student.complaints')->with('success', 'Complaint submitted successfully. We will review it shortly.');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:2048',
        ]);

        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return response()->json(['error' => 'Student profile not found.'], 404);
        }

        if ($request->hasFile('document')) {
            $file = $request->file('document');
            $filename = time() . '_' . $student->id . '_' . $request->document_type . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('student_documents', $filename, 'public');

            // In a real system, you'd save this to a student_documents table
            // For now, we'll just return success
            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'path' => $path
            ]);
        }

        return response()->json(['error' => 'No file uploaded.'], 400);
    }

    public function documents()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        // In a real system, you'd fetch documents from a student_documents table
        // For now, we'll just show the form
        return view('student.documents', compact('student'));
    }

    public function checkProgression()
    {
        $user = Auth::user();
        $student = Student::where('email', $user->email)->first();

        if (!$student) {
            return redirect()->route('login')->with('error', 'Student profile not found.');
        }

        // Simple progression logic - check if student has passing grades
        $currentSession = AcademicSession::where('is_active', true)->first();
        $currentTerm = Term::where('is_active', true)->first();

        $grades = StudentGrade::where('student_id', $student->id)
            ->where('academic_session_id', $currentSession?->id)
            ->where('term_id', $currentTerm?->id)
            ->get();

        $averageGrade = $grades->avg('score');
        $canProgress = $averageGrade >= 50; // Simple threshold

        return view('student.progression', compact('student', 'grades', 'averageGrade', 'canProgress'));
    }
}
