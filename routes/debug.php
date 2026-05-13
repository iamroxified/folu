<?php
use Illuminate\Support\Facades\Route;

Route::get('/debug-session', function () {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $authCheck = auth()->check() ? 'true' : 'false';
    $_SESSION['auth_check'] = $authCheck;
    return response()->json([
        'auth_check' => $authCheck,
        'laravel_session' => session()->all(),
        'php_session' => $_SESSION,
    ]);
});
