<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class StudentPortalController extends Controller
{
    public function show(?string $path = 'login.php'): View
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (\Illuminate\Support\Facades\Auth::check()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            $student = \App\Models\Student::where('email', $user->email)->first(); // Note: adjust if needed, but the view uses helpers

            $_SESSION['student_user_id'] = $user->id;
            $_SESSION['student_id'] = $student->id ?? 0;
            $_SESSION['student_username'] = $user->username ?? $user->name;
            $_SESSION['student_name'] = $user->name;
        }

        $_SERVER['REQUEST_METHOD'] = request()->method();
        $_SERVER['REQUEST_URI'] = '/' . ltrim(request()->path(), '/');
        $_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
        $_GET = request()->query();
        $_POST = request()->post();
        $_REQUEST = array_merge($_GET, $_POST);

        $view = 'student.pages.' . $this->normalizePath($path);

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
