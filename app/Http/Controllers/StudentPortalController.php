<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StudentPortalController extends Controller
{
    public function show(?string $path = 'login.php')
    {
        $normalized = $this->normalizePath($path);
        if ($normalized === 'index' || $normalized === 'dashboard') {
            return redirect()->route('student.dashboard');
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            try {
                $student = \App\Models\Student::where('email', $user->email)
                    ->orWhere('admission_no', $user->username)
                    ->orWhere('student_number', $user->username)
                    ->orWhere('user_link', $user->id)
                    ->first();

                if ($student && empty($student->user_link)) {
                    $student->user_link = $user->id;
                    $student->save();
                }
            } catch (\Throwable $e) {
                $student = null;
            }

            $_SESSION['student_user_id'] = $user->id;
            $_SESSION['student_id'] = $student->id ?? 0;
            $_SESSION['student_username'] = $student->admission_no ?? ($user->username ?? $user->name);
            $_SESSION['student_name'] = $student ? trim($student->first_name . ' ' . $student->last_name) : $user->name;
        }

        $_SERVER['REQUEST_METHOD'] = request()->method();
        $_SERVER['REQUEST_URI'] = '/' . ltrim(request()->path(), '/');
        $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
        $_GET = request()->query();
        $_POST = request()->post();
        $_REQUEST = array_merge($_GET, $_POST);

        $view = 'student.pages.' . $normalized;

        abort_unless(view()->exists($view), 404);

        return view($view);
    }

    private function normalizePath(?string $path): string
    {
        $path = trim((string) $path, '/');
        $path = $path === '' ? 'login' : $path;
        $path = preg_replace('/\.(html|php)$/i', '', $path) ?: 'login';

        return str_replace('/', '.', $path);
    }
}
