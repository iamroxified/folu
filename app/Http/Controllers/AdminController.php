<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AcademicSession;
use App\Models\Term;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\FeeStructure;
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolSetting;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:Admin');
    }

    public function dashboard()
    {
        $stats = [
            'total_students' => Student::count(),
            'total_staff' => User::where('role_id', '!=', Role::where('role_name', 'Student')->first()->id)->count(),
            'active_sessions' => AcademicSession::where('is_active', true)->count(),
            'pending_admissions' => Student::where('admission_status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }

    // Student Management
    public function students()
    {
        $students = Student::with(['currentClass', 'currentSession', 'currentTerm'])->paginate(20);
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
            'gender' => 'required|in:male,female,other',
            'admission_status' => 'required|in:pending,admitted,withdrawn',
            'category' => 'required|in:NI,OS',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_class_id' => 'nullable|exists:school_classes,id',
            'current_session_id' => 'nullable|exists:academic_sessions,id',
            'current_term_id' => 'nullable|exists:terms,id',
        ]);

        $data = $request->all();

        if ($request->hasFile('passport')) {
            $data['passport'] = $request->file('passport')->store('passports', 'public');
        }

        $data['student_number'] = 'STU' . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);
        $data['enrollment_date'] = now();

        Student::create($data);

        return redirect()->route('admin.students')->with('success', 'Student created successfully.');
    }

    public function showStudent(Student $student)
    {
        $student->load(['currentClass', 'currentSession', 'currentTerm']);
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
            'gender' => 'required|in:male,female,other',
            'admission_status' => 'required|in:pending,admitted,withdrawn',
            'category' => 'required|in:NI,OS',
            'passport' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'current_class_id' => 'nullable|exists:school_classes,id',
            'current_session_id' => 'nullable|exists:academic_sessions,id',
            'current_term_id' => 'nullable|exists:terms,id',
        ]);

        $data = $request->all();

        if ($request->hasFile('passport')) {
            // Delete old passport if exists
            if ($student->passport) {
                Storage::disk('public')->delete($student->passport);
            }
            $data['passport'] = $request->file('passport')->store('passports', 'public');
        }

        $student->update($data);

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
            // Automatically switch NI to OS after first promotion
            if ($student->category === 'NI') {
                $student->category = 'OS';
            }
            
            $student->current_class_id = $request->to_class_id;
            $student->current_session_id = $request->to_session_id;
            $student->current_term_id = $request->to_term_id;
            $student->save();
            $promotedCount++;
        }

        return redirect()->route('admin.students.promotions')->with('success', "Successfully promoted $promotedCount students.");
    }

    // Sessions Management
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

    // Terms Management
    public function terms()
    {
        $terms = Term::all();
        return view('admin.terms.index', compact('terms'));
    }

    // Classes Management
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

    // Subjects Management
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
            'term_id' => 'required|exists:terms,id',
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        FeeStructure::create($request->all());

        return redirect()->route('admin.fees')->with('success', 'Fee structure created successfully.');
    }

    // Staff Management
    public function staff()
    {
        $staff = User::with('role')->where('role_id', '!=', Role::where('role_name', 'Student')->first()->id)->paginate(20);
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

    // Students Import/Export
    public function importStudents(Request $request)
    {
        $request->validate(['csv_file' => 'required|file|mimes:csv,txt']);
        // Parse CSV logic here...
        return redirect()->route('admin.students')->with('success', 'Students imported (simulation).');
    }

    public function exportStudents()
    {
        $students = Student::all();
        // Export logic here...
        return response()->streamDownload(function() use ($students) {
            echo "student_number,first_name,last_name,email\n";
            foreach($students as $student) echo "{$student->student_number},{$student->first_name},{$student->last_name},{$student->email}\n";
        }, 'students.csv');
    }

    // Settings
    public function settings()
    {
        $settings = SchoolSetting::first();
        $sessions = AcademicSession::all();
        $terms = Term::all();
        $activeSession = AcademicSession::where('is_active', true)->first();
        $activeTerm = Term::where('is_active', true)->first();
        return view('admin.settings.index', compact('settings', 'sessions', 'terms', 'activeSession', 'activeTerm'));
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'school_name' => 'required|string',
            'school_email' => 'required|email',
            'school_phone' => 'required|string',
            'school_address' => 'nullable|string',
            'active_session_id' => 'nullable|exists:academic_sessions,id',
            'active_term_id' => 'nullable|exists:terms,id',
        ]);

        $settings = SchoolSetting::first();
        if ($settings) {
            $settings->update($request->only(['school_name', 'school_email', 'school_phone', 'school_address']));
        } else {
            SchoolSetting::create($request->only(['school_name', 'school_email', 'school_phone', 'school_address']));
        }

        if ($request->has('active_session_id')) {
            AcademicSession::where('is_active', true)->update(['is_active' => false]);
            AcademicSession::where('id', $request->active_session_id)->update(['is_active' => true]);
        }

        if ($request->has('active_term_id')) {
            Term::where('is_active', true)->update(['is_active' => false]);
            Term::where('id', $request->active_term_id)->update(['is_active' => true]);
        }

        return redirect()->route('admin.settings')->with('success', 'Settings updated.');
    }

    // Announcements
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

    // Timetable & Assignments (Ported/Linked logic)
    public function timetable()
    {
        // Re-use legacy view if desired or new one
        return view('admin.classes.timetable');
    }

    public function assignments()
    {
        // Re-use legacy view if desired or new one
        return view('admin.classes.assignments');
    }
}
