<?php

use App\Http\Controllers\AccountSettingsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DesignPreviewController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PublicAnnouncementController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\StudentPortalController;
use App\Http\Controllers\SubAdminController;
use App\Http\Controllers\SubAdminStudentController;
use App\Http\Controllers\TeacherScannerController;
use App\Http\Controllers\WelcomeController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('home');
Route::get('/school-announcements', PublicAnnouncementController::class)->name('public-announcements.index');
Route::get('/school-history', fn () => view('school-history', ['schoolSettings' => Setting::current()]))->name('school-history');
Route::get('/teachers', fn () => view('teachers', ['schoolSettings' => Setting::current()]))->name('teachers');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::get('/register', [RegistrationController::class, 'create'])->name('register');

Route::middleware('guest')->group(function () {
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/register', [RegistrationController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/register/student', [RegistrationController::class, 'storeStudent'])->name('register.student')->middleware('throttle:5,1');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email')->middleware('throttle:5,1');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store')->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/verify-otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/verify-otp', [OtpController::class, 'verify'])->name('otp.verify')->middleware('throttle:10,1');
    Route::post('/verify-otp/resend', [OtpController::class, 'resend'])->name('otp.resend')->middleware('throttle:3,1');
    Route::post('/verify-otp/cancel', [OtpController::class, 'cancel'])->name('otp.cancel');
    Route::get('/account-settings', [AccountSettingsController::class, 'edit'])->name('account-settings.edit');
    Route::put('/account-settings', [AccountSettingsController::class, 'update'])->name('account-settings.update');
    Route::middleware('role:admin,sub_admin,guard')->prefix('scanner')->name('scanner.')->group(function () {
        Route::get('/', [ScannerController::class, 'index'])->name('index');
        Route::post('/verify', [ScannerController::class, 'verify'])->name('verify');
        Route::get('/directory', [ScannerController::class, 'directory'])->name('directory');
        Route::post('/sync', [ScannerController::class, 'sync'])->name('sync');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('students', StudentController::class)->except(['edit']);
        Route::patch('/students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
        Route::patch('/students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
        Route::get('/students/{student}/qr-code/download', [QrCodeController::class, 'download'])->name('students.qr-code.download');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::patch('/users/{user}/set-password', [UserController::class, 'setPassword'])->name('users.set-password');
        Route::post('/users/accounts', [UserController::class, 'storeAccount'])->name('users.store-account');
        Route::resource('guardians', GuardianController::class)->except(['index', 'edit']);
        Route::post('/guardians/{guardian}/students', [GuardianController::class, 'linkStudent'])->name('guardians.students.link');
        Route::delete('/guardians/{guardian}/students/{student}', [GuardianController::class, 'unlinkStudent'])->name('guardians.students.unlink');
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::get('/design-preview', [DesignPreviewController::class, 'index'])->name('design-preview');
        Route::resource('sections', SectionController::class)->except(['edit']);
        Route::patch('/sections/{section}/toggle-status', [SectionController::class, 'toggleStatus'])->name('sections.toggle-status');
        Route::resource('subjects', SubjectController::class)->except(['edit']);
        Route::patch('/subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])->name('subjects.toggle-status');
    });

    Route::middleware('role:admin,sub_admin,teacher')->prefix('class-attendance')->name('teacher-scanner.')->group(function () {
        Route::get('/scanner', [TeacherScannerController::class, 'index'])->name('index');
        Route::post('/scan', [TeacherScannerController::class, 'verify'])->name('verify');
    });

    Route::middleware('role:sub_admin,teacher')->prefix('sub-admin')->name('sub-admin.')->group(function () {
        Route::get('/dashboard', [SubAdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/students', [SubAdminStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}', [SubAdminStudentController::class, 'show'])->name('students.show');
        Route::get('/sections', [SubAdminStudentController::class, 'sections'])->name('sections.index');
        Route::get('/sections/{section}/students', [SubAdminStudentController::class, 'section'])->name('sections.students');
        Route::get('/roster', [SubAdminStudentController::class, 'roster'])->name('roster.index');
        Route::get('/roster/export/pdf', [SubAdminStudentController::class, 'exportPdf'])->name('roster.export-pdf');
        Route::get('/roster/export/excel', [SubAdminStudentController::class, 'exportExcel'])->name('roster.export-excel');
        Route::get('/roster/export/csv', [SubAdminStudentController::class, 'exportCsv'])->name('roster.export-csv');
    });

    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'index'])->name('dashboard');
        Route::get('/students/{student}/dashboard', [ParentPortalController::class, 'dashboard'])->name('students.dashboard');
        Route::get('/students/{student}/attendance', [ParentPortalController::class, 'attendance'])->name('students.attendance');
        Route::get('/announcements', [ParentPortalController::class, 'announcements'])->name('announcements');
        Route::get('/link-student', [ParentPortalController::class, 'linkStudent'])->name('link-student.index');
        Route::post('/link-student', [ParentPortalController::class, 'storeLinkRequest'])->name('link-student.store');
        Route::delete('/link-requests', [ParentPortalController::class, 'destroyAllLinkRequests'])->name('link-requests.destroy-all');
        Route::delete('/link-requests/{linkRequest}', [ParentPortalController::class, 'destroyLinkRequest'])->name('link-requests.destroy');
    });

    Route::middleware('role:guard')->prefix('guard')->name('guard.')->group(function () {
        Route::get('/dashboard', [GuardController::class, 'dashboard'])->name('dashboard');
    });

    Route::middleware('role:admin,sub_admin,teacher')->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
        Route::patch('/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('toggle-pin');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/{id}/read', [NotificationController::class, 'readAndRedirect'])->name('read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
    });
});

Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [StudentPortalController::class, 'attendance'])->name('attendance');
    Route::redirect('/profile', '/account-settings')->name('profile');
    Route::post('/link-requests/{linkRequest}/accept', [StudentPortalController::class, 'acceptLinkRequest'])->name('link-requests.accept');
    Route::post('/link-requests/{linkRequest}/deny', [StudentPortalController::class, 'denyLinkRequest'])->name('link-requests.deny');
    Route::get('/qr-code', [QrCodeController::class, 'showForStudent'])->name('qr-code');
    Route::get('/qr-code/download', [QrCodeController::class, 'downloadForStudent'])->name('qr-code.download');
});
