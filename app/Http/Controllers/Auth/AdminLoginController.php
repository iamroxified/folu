<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember_me'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $isAdmin = false;

            // Check if user is an admin based on the role mapping in update_users_table.sql
            if (isset($user->role_id) && (int) $user->role_id === 1) {
                $isAdmin = true;
            } else {
                // Fallback to legacy string role checks
                $role = strtolower(trim((string) ($user->role ?? $user->access ?? '')));
                $allowedRoles = ['admin', 'super_admin', 'superadmin', 'administrator', 'manager'];

                if (in_array($role, $allowedRoles)) {
                    $isAdmin = true;
                }
            }

            if ($isAdmin) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Not authorized
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->with('error', 'Your account does not have administrative privileges.');
        }

        return back()->withErrors([
            'username' => 'The provided credentials do not match our records.',
        ])->onlyInput('username');
    }
}
