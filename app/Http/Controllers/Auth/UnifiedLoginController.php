<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class UnifiedLoginController extends Controller
{
    /**
     * Show the unified multi-category login form.
     */
    public function showLoginForm(Request $request)
    {
        if (Auth::check()) {
            return $this->redirectUserBasedOnRole(Auth::user());
        }

        $activeRole = strtolower((string) $request->query('role', 'student'));
        if (!in_array($activeRole, ['student', 'parent', 'staff', 'admin'], true)) {
            $activeRole = 'student';
        }

        return view('auth.unified_login', [
            'activeRole' => $activeRole,
        ]);
    }

    /**
     * Handle authentication for all user categories (Student, Parent, Staff, Admin).
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'role' => ['nullable', 'string'],
        ]);

        $loginInput = trim($credentials['username']);
        $password = $credentials['password'];
        $requestedRole = strtolower(trim((string) ($request->input('role') ?? '')));
        $remember = $request->boolean('remember_me');

        // Find user by username, email, or admission number/staff ID
        $user = $this->findUserByInput($loginInput);

        if (!$user) {
            return back()->withErrors([
                'username' => 'No account found matching those credentials.',
            ])->withInput($request->only('username', 'role'));
        }

        // Verify status
        $status = strtolower((string) ($user->status ?? 'active'));
        if (in_array($status, ['inactive', 'suspended', 'dismissed', 'blocked'], true)) {
            return back()->with('error', 'Your account is currently ' . $status . '. Please contact the school administration.')
                ->withInput($request->only('username', 'role'));
        }

        // Verify password
        if (!Hash::check($password, $user->password) && !password_verify($password, $user->password)) {
            return back()->withErrors([
                'password' => 'Incorrect password. Please try again.',
            ])->withInput($request->only('username', 'role'));
        }

        // Authenticate in Laravel Auth
        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Sync legacy PHP session variables for backwards compatibility
        $this->syncLegacySession($user);

        return $this->redirectUserBasedOnRole($user);
    }

    /**
     * Locate user by username, email, admission_no, or staff_id
     */
    protected function findUserByInput(string $loginInput): ?User
    {
        // 1. Direct match on username or email
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if ($user) {
            return $user;
        }

        // 2. Search student admission number
        try {
            $student = DB::table('students')
                ->where('admission_no', $loginInput)
                ->first();

            if ($student && !empty($student->user_link)) {
                $user = User::find($student->user_link);
                if ($user) return $user;
            }

            if ($student && !empty($student->user_id)) {
                $user = User::find($student->user_id);
                if ($user) return $user;
            }
        } catch (Throwable $e) {}

        // 3. Search teacher/staff ID or email
        try {
            $teacher = DB::table('teachers')
                ->where('teacher_id', $loginInput)
                ->orWhere('email', $loginInput)
                ->first();

            if ($teacher && !empty($teacher->user_id)) {
                $user = User::find($teacher->user_id);
                if ($user) return $user;
            }
        } catch (Throwable $e) {}

        // 4. Search parent email/phone
        try {
            $parent = DB::table('parents')
                ->where('email', $loginInput)
                ->orWhere('phone', $loginInput)
                ->orWhere('parent_id', $loginInput)
                ->first();

            if ($parent && !empty($parent->user_id)) {
                $user = User::find($parent->user_id);
                if ($user) return $user;
            }
        } catch (Throwable $e) {}

        return null;
    }

    /**
     * Synchronize legacy PHP session variables for legacy non-Laravel files.
     */
    protected function syncLegacySession(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        $roleName = strtolower($this->getUserRoleName($user));

        if (in_array($roleName, ['admin', 'super_admin', 'superadmin', 'administrator', 'manager'], true) || (isset($user->role_id) && (int)$user->role_id === 1)) {
            $_SESSION['adid'] = $user->id;
            $_SESSION['username'] = $user->username ?? $user->name;
            $_SESSION['email'] = $user->email ?? '';
        } elseif ($roleName === 'student' || (isset($user->role_id) && (int)$user->role_id === 4)) {
            $_SESSION['student_user_id'] = $user->id;
            $_SESSION['student_username'] = $user->username ?? $user->name;
            $_SESSION['student_name'] = $user->name;

            try {
                $student = DB::table('students')
                    ->where('email', $user->email)
                    ->orWhere('user_link', $user->id)
                    ->orWhere('user_id', $user->id)
                    ->first();
                if ($student) {
                    $_SESSION['student_id'] = $student->id;
                }
            } catch (Throwable $e) {}
        } elseif (in_array($roleName, ['teacher', 'accountant', 'staff'], true) || (isset($user->role_id) && in_array((int)$user->role_id, [2, 3], true))) {
            $_SESSION['teacher_user_id'] = $user->id;
            $_SESSION['teacher_username'] = $user->username ?? $user->name;
            $_SESSION['teacher_name'] = $user->name;

            try {
                $staff = DB::table('teachers')
                    ->where('email', $user->email)
                    ->orWhere('user_id', $user->id)
                    ->first();
                if ($staff) {
                    $_SESSION['teacher_id'] = $staff->id;
                }
            } catch (Throwable $e) {}
        }
    }

    /**
     * Determine role string for user.
     */
    protected function getUserRoleName(User $user): string
    {
        if (isset($user->role) && is_object($user->role) && isset($user->role->role_name)) {
            return (string) $user->role->role_name;
        }

        if (isset($user->role) && is_string($user->role)) {
            return $user->role;
        }

        if (isset($user->role_id)) {
            $roleMap = [
                1 => 'Admin',
                2 => 'Teacher',
                3 => 'Accountant',
                4 => 'Student',
                5 => 'Parent',
            ];
            return $roleMap[(int)$user->role_id] ?? 'Student';
        }

        return 'Student';
    }

    /**
     * Redirect user based on their primary role.
     */
    protected function redirectUserBasedOnRole(User $user)
    {
        $roleName = strtolower($this->getUserRoleName($user));

        if (in_array($roleName, ['admin', 'super_admin', 'superadmin', 'administrator', 'manager'], true) || (isset($user->role_id) && (int)$user->role_id === 1)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        if ($roleName === 'teacher' || (isset($user->role_id) && (int)$user->role_id === 2)) {
            return redirect()->intended(route('teacher.dashboard'));
        }

        if ($roleName === 'accountant' || (isset($user->role_id) && (int)$user->role_id === 3)) {
            return redirect()->intended(route('accountant.dashboard'));
        }

        if ($roleName === 'student' || $roleName === 'parent' || (isset($user->role_id) && (int)$user->role_id === 4)) {
            return redirect()->intended(route('student.dashboard'));
        }

        return redirect()->intended('/');
    }

    /**
     * Log out user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }

        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
