<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentGrade;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicSession;
use App\Models\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Teacher');
    }

    public function dashboard()
    {
        $teacher = Auth::user();
        // Assuming teacher has assigned classes - for now, get all classes
        $assignedClasses = SchoolClass::all(); // TODO: Filter by teacher's assignments
        $activeSession = AcademicSession::where('is_active', true)->first();
        $activeTerm = Term::first(); // TODO: Get active term

        return view('teacher.dashboard', compact('assignedClasses', 'activeSession', 'activeTerm'));
    }

    // Attendance Management
    public function attendance()
    {
        $teacher = Auth::user();
        $activeSession = AcademicSession::where('is_active', true)->first();
        $activeTerm = Term::first(); // TODO: Get active term
        $assignedClasses = SchoolClass::all(); // TODO: Filter by teacher's assignments

        return view('teacher.attendance.index', compact('assignedClasses', 'activeSession', 'activeTerm'));
    }

    public function markAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent',
            'attendance.*.reason' => 'nullable|string',
        ]);

        foreach ($request->attendance as $attendanceData) {
            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $attendanceData['student_id'],
                    'school_class_id' => $request->class_id,
                    'session_id' => $request->session_id,
                    'term_id' => $request->term_id,
                    'date' => $request->date,
                ],
                [
                    'status' => $attendanceData['status'],
                    'reason' => $attendanceData['reason'] ?? null,
                    'marked_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Attendance marked successfully.');
    }

    public function viewAttendance(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'date' => 'required|date',
        ]);

        $students = Student::where('current_class_id', $request->class_id)
                          ->where('current_session_id', $request->session_id)
                          ->where('current_term_id', $request->term_id)
                          ->get();

        $attendance = StudentAttendance::where('school_class_id', $request->class_id)
                                      ->where('session_id', $request->session_id)
                                      ->where('term_id', $request->term_id)
                                      ->where('date', $request->date)
                                      ->get()
                                      ->keyBy('student_id');

        return view('teacher.attendance.view', compact('students', 'attendance', 'request'));
    }

    // Results Management
    public function results()
    {
        $teacher = Auth::user();
        $activeSession = AcademicSession::where('is_active', true)->first();
        $activeTerm = Term::first(); // TODO: Get active term
        $assignedClasses = SchoolClass::all(); // TODO: Filter by teacher's assignments

        $subjects = Subject::all();
        return view('teacher.results.index', compact('assignedClasses', 'activeSession', 'activeTerm', 'subjects'));
    }

    public function enterResults(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
            'grades' => 'required|array',
            'grades.*.student_id' => 'required|exists:students,id',
            'grades.*.ca1' => 'nullable|numeric|min:0|max:100',
            'grades.*.ca2' => 'nullable|numeric|min:0|max:100',
            'grades.*.exam' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($request->grades as $gradeData) {
            $total = ($gradeData['ca1'] ?? 0) + ($gradeData['ca2'] ?? 0) + ($gradeData['exam'] ?? 0);

            StudentGrade::updateOrCreate(
                [
                    'student_id' => $gradeData['student_id'],
                    'class_id' => $request->class_id,
                    'subject_id' => $request->subject_id,
                    'academic_session_id' => $request->session_id,
                    'term_id' => $request->term_id,
                ],
                [
                    'ca1_score' => $gradeData['ca1'] ?? null,
                    'ca2_score' => $gradeData['ca2'] ?? null,
                    'exam_score' => $gradeData['exam'] ?? null,
                    'total_score' => $total,
                    'grade' => $this->calculateGrade($total),
                    'entered_by' => Auth::id(),
                ]
            );
        }

        return redirect()->back()->with('success', 'Results entered successfully.');
    }

    public function viewResults(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'session_id' => 'required|exists:academic_sessions,id',
            'term_id' => 'required|exists:terms,id',
        ]);

        $students = Student::where('current_class_id', $request->class_id)
                          ->where('current_session_id', $request->session_id)
                          ->where('current_term_id', $request->term_id)
                          ->get();

        $grades = StudentGrade::where('class_id', $request->class_id)
                             ->where('subject_id', $request->subject_id)
                             ->where('academic_session_id', $request->session_id)
                             ->where('term_id', $request->term_id)
                             ->get()
                             ->keyBy('student_id');

        return view('teacher.results.view', compact('students', 'grades', 'request'));
    }

    private function calculateGrade($total)
    {
        if ($total >= 90) return 'A+';
        if ($total >= 80) return 'A';
        if ($total >= 70) return 'B';
        if ($total >= 60) return 'C';
        if ($total >= 50) return 'D';
        return 'F';
    }
}
