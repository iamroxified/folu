<?php

use App\Http\Controllers\AccountantController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\UnifiedLoginController;
use App\Http\Controllers\LegacyAdminController;
use App\Http\Controllers\LegacyFrontendController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\TeacherPortalController;
use App\Http\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// Unified Authentication Routes
Route::get('/login', [UnifiedLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [UnifiedLoginController::class, 'login'])->name('login.submit');
Route::get('/logout', [UnifiedLoginController::class, 'logout'])->name('logout');

// Specialized login redirects
Route::get('/admin/login', fn() => redirect()->route('login', ['role' => 'admin']))->name('admin.login');
Route::post('/admin/login', [UnifiedLoginController::class, 'login'])->name('admin.login.submit');

Route::get('/api/lgas', [LegacyFrontendController::class, 'getLgas'])->name('api.lgas');
Route::get('/fetch_lgas.php', [LegacyFrontendController::class, 'getLgas']);
Route::post('/apply', [LegacyFrontendController::class, 'submitApplication'])->name('apply.submit');

// Placeholder for the password reset route referenced in the login view
Route::get('/password/reset', function () { return 'Password reset page coming soon.'; })->name('password.request');

Route::middleware(['auth', 'role:Admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Promotions
    Route::get('/students/promotions', [AdminController::class, 'promotions'])->name('students.promotions');
    Route::post('/students/promotions', [AdminController::class, 'processPromotions'])->name('students.promotions.process');

    // Students
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('students.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('students.store');
    Route::post('/students/import', [AdminController::class, 'importStudents'])->name('students.import');
    Route::get('/students/export', [AdminController::class, 'exportStudents'])->name('students.export');
    Route::get('/students/{student}', [AdminController::class, 'showStudent'])->name('students.show');
    Route::get('/students/{student}/admission-letter', [AdminController::class, 'admissionLetter'])->name('students.admission_letter');

    // Profile & Settings
    Route::get('/profile', [AdminController::class, 'profile'])->name('profile');
    Route::post('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [AdminController::class, 'updatePassword'])->name('profile.password');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');

    // Announcements
    Route::get('/announcements', [AdminController::class, 'announcements'])->name('announcements');
    Route::post('/announcements', [AdminController::class, 'storeAnnouncement'])->name('announcements.store');

    // Timetable & Assignments
    Route::get('/classes/timetable', [AdminController::class, 'timetable'])->name('classes.timetable');
    Route::get('/classes/assignments', [AdminController::class, 'assignments'])->name('classes.assignments');
    Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('students.edit');
    Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('students.update');
    Route::delete('/students/{student}', [AdminController::class, 'destroyStudent'])->name('students.destroy');

    // Sessions
    Route::get('/sessions', [AdminController::class, 'sessions'])->name('sessions');
    Route::get('/sessions/create', [AdminController::class, 'createSession'])->name('sessions.create');
    Route::post('/sessions', [AdminController::class, 'storeSession'])->name('sessions.store');

    // Terms
    Route::get('/terms', [AdminController::class, 'terms'])->name('terms');
    Route::get('/terms/create', [AdminController::class, 'createTerm'])->name('terms.create');
    Route::post('/terms', [AdminController::class, 'storeTerm'])->name('terms.store');

    // Classes
    Route::get('/classes', [AdminController::class, 'classes'])->name('classes');
    Route::get('/classes/create', [AdminController::class, 'createClass'])->name('classes.create');
    Route::post('/classes', [AdminController::class, 'storeClass'])->name('classes.store');

    // Subjects
    Route::get('/subjects', [AdminController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/create', [AdminController::class, 'createSubject'])->name('subjects.create');
    Route::post('/subjects', [AdminController::class, 'storeSubject'])->name('subjects.store');

    // Fees
    Route::get('/fees', [AdminController::class, 'fees'])->name('fees');
    Route::get('/fees/create', [AdminController::class, 'createFee'])->name('fees.create');
    Route::post('/fees', [AdminController::class, 'storeFee'])->name('fees.store');
    Route::get('/fees/{fee}/edit', [AdminController::class, 'editFee'])->name('fees.edit');
    Route::put('/fees/{fee}', [AdminController::class, 'updateFee'])->name('fees.update');
    Route::delete('/fees/{fee}', [AdminController::class, 'destroyFee'])->name('fees.destroy');
    Route::post('/fees/additional-charge', [AdminController::class, 'storeAdditionalCharge'])->name('fees.additional_charge');
    Route::get('/students/import-failed-download', [AdminController::class, 'downloadFailedImports'])->name('students.import_failed_download');

    // Staff
    Route::get('/staff', [AdminController::class, 'staff'])->name('staff');
    Route::get('/staff/create', [AdminController::class, 'createStaff'])->name('staff.create');
    Route::post('/staff', [AdminController::class, 'storeStaff'])->name('staff.store');
    Route::get('/staff/{staff}/details', [AdminController::class, 'getStaffDetails'])->name('staff.details');

    // Payroll Management
    Route::get('/payroll', [AdminController::class, 'payroll'])->name('payroll');
    Route::get('/payroll/create', [AdminController::class, 'createPayroll'])->name('payroll.create');
    Route::post('/payroll', [AdminController::class, 'storePayroll'])->name('payroll.store');
    Route::get('/payroll/{payroll}', [AdminController::class, 'showPayroll'])->name('payroll.show');
    Route::get('/payroll/{payroll}/edit', [AdminController::class, 'editPayroll'])->name('payroll.edit');
    Route::put('/payroll/{payroll}', [AdminController::class, 'updatePayroll'])->name('payroll.update');
    Route::delete('/payroll/{payroll}', [AdminController::class, 'destroyPayroll'])->name('payroll.destroy');
    Route::get('/payroll/{payroll}/receipt', [AdminController::class, 'payrollReceipt'])->name('payroll.receipt');
    Route::get('/payroll/export/csv', [AdminController::class, 'exportPayroll'])->name('payroll.export');

    // Expenditure Module
    Route::get('/expenditures', [AdminController::class, 'expenditures'])->name('expenditures');
    Route::get('/expenditures/create', [AdminController::class, 'createExpenditure'])->name('expenditures.create');
    Route::post('/expenditures', [AdminController::class, 'storeExpenditure'])->name('expenditures.store');
    Route::get('/expenditures/{expenditure}', [AdminController::class, 'showExpenditure'])->name('expenditures.show');
    Route::get('/expenditures/{expenditure}/edit', [AdminController::class, 'editExpenditure'])->name('expenditures.edit');
    Route::put('/expenditures/{expenditure}', [AdminController::class, 'updateExpenditure'])->name('expenditures.update');
    Route::delete('/expenditures/{expenditure}', [AdminController::class, 'destroyExpenditure'])->name('expenditures.destroy');
    Route::get('/expenditures/export/csv', [AdminController::class, 'exportExpenditures'])->name('expenditures.export');
});

Route::middleware(['auth', 'role:Teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::redirect('/', '/teacher/dashboard');
    Route::get('/dashboard', [App\Http\Controllers\TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [App\Http\Controllers\TeacherController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/mark', [App\Http\Controllers\TeacherController::class, 'markAttendance'])->name('attendance.mark');
    Route::get('/attendance/view', [App\Http\Controllers\TeacherController::class, 'viewAttendance'])->name('attendance.view');

    Route::get('/results', [App\Http\Controllers\TeacherController::class, 'results'])->name('results');
    Route::post('/results/enter', [App\Http\Controllers\TeacherController::class, 'enterResults'])->name('results.enter');
    Route::get('/results/view', [App\Http\Controllers\TeacherController::class, 'viewResults'])->name('results.view');
});

Route::middleware(['auth', 'role:Accountant'])->prefix('accountant')->name('accountant.')->group(function () {
    Route::get('/dashboard', [AccountantController::class, 'dashboard'])->name('dashboard');
    Route::get('/fees', [AccountantController::class, 'fees'])->name('fees');
    Route::post('/fees/additional-charge', [AccountantController::class, 'storeAdditionalCharge'])->name('fees.additional_charge');
    Route::get('/payments', [AccountantController::class, 'payments'])->name('payments');
    Route::post('/payments/record', [AccountantController::class, 'recordPayment'])->name('payments.record');
    Route::get('/get-student-fees/{studentId}', [AccountantController::class, 'getStudentFees'])->name('get-student-fees');
    Route::get('/payroll', [AccountantController::class, 'payroll'])->name('payroll');
    Route::post('/payroll/create', [AccountantController::class, 'createPayroll'])->name('payroll.create');
    Route::get('/reports', [AccountantController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [AccountantController::class, 'exportReport'])->name('reports.export');
});

Route::middleware(['auth', 'role:Student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [StudentController::class, 'profile'])->name('profile');
    Route::get('/results', [StudentController::class, 'results'])->name('results');
    Route::get('/payments', [StudentController::class, 'payments'])->name('payments');
    Route::get('/receipts', [StudentController::class, 'receipts'])->name('receipts');
    Route::get('/complaints', [StudentController::class, 'complaints'])->name('complaints');
    Route::post('/complaints/submit', [StudentController::class, 'submitComplaint'])->name('complaints.submit');
    Route::post('/upload-document', [StudentController::class, 'uploadDocument'])->name('upload.document');
    Route::get('/documents', [StudentController::class, 'documents'])->name('documents');
    Route::get('/progression', [StudentController::class, 'checkProgression'])->name('progression');
});

Route::redirect('/admin', '/admin/login');
Route::redirect('/teacher', '/teacher/login.php');
Route::redirect('/student', '/student/login.php');
Route::redirect('/accountant', '/accountant/login.php');

Route::match(['get', 'post'], '/admin/{path}', [LegacyAdminController::class, 'show'])
    ->where('path', '.*')
    ->name('admin.legacy');

Route::match(['get', 'post'], '/teacher/{path}', [TeacherPortalController::class, 'show'])
    ->where('path', '.*')
    ->name('teacher.portal');

Route::match(['get', 'post'], '/student/{path}', [StudentPortalController::class, 'show'])
    ->where('path', '.*')
    ->name('student.portal');

Route::get('/', [LegacyFrontendController::class, 'show'])
    ->defaults('', 'index')
    ->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/{page}', [LegacyFrontendController::class, 'show'])
    ->where('page', '[^/]+')
    ->name('frontend.page');
