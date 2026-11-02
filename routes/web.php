<?php

use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\QrCodeController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\GuardianController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\Admin\SettingController;
use App\Models\Setting;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\DesignPreviewController;
use App\Http\Controllers\SubAdminController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\StudentPortalController;

Route::get('/', function () {
    return view('welcome', ['schoolSettings' => Setting::current()]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:5,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::middleware('role:admin,sub_admin,guard')->prefix('scanner')->name('scanner.')->group(function () {
        Route::get('/', [ScannerController::class, 'index'])->name('index');
        Route::post('/verify', [ScannerController::class, 'verify'])->name('verify');
        Route::get('/directory', [ScannerController::class, 'directory'])->name('directory');
        Route::post('/sync', [ScannerController::class, 'sync'])->name('sync');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('students', StudentController::class);
        Route::patch('/students/{student}/toggle-status', [StudentController::class, 'toggleStatus'])->name('students.toggle-status');
        Route::get('/students/{student}/qr-code', [QrCodeController::class, 'show'])->name('students.qr-code.show');
        Route::patch('/students/{student}/qr-code/regenerate', [QrCodeController::class, 'regenerate'])->name('students.qr-code.regenerate');
        Route::get('/students/{student}/qr-code/download', [QrCodeController::class, 'download'])->name('students.qr-code.download');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export-pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export-excel');
        Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::resource('guardians', GuardianController::class);
        Route::post('/guardians/{guardian}/students', [GuardianController::class, 'linkStudent'])->name('guardians.students.link');
        Route::delete('/guardians/{guardian}/students/{student}', [GuardianController::class, 'unlinkStudent'])->name('guardians.students.unlink');
        Route::post('/guardians/{guardian}/create-account', [GuardianController::class, 'createParentAccount'])->name('guardians.create-account');
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/{log}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('/backups', [BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups', [BackupController::class, 'store'])->name('backups.store');
        Route::get('/backups/{filename}/download', [BackupController::class, 'download'])->name('backups.download');
        Route::delete('/backups/{filename}', [BackupController::class, 'destroy'])->name('backups.destroy');
        Route::post('/backups/{filename}/restore', [BackupController::class, 'restore'])->name('backups.restore');
        Route::get('/design-preview', [DesignPreviewController::class, 'index'])->name('design-preview');
        Route::resource('sections', SectionController::class);
        Route::patch('/sections/{section}/toggle-status', [SectionController::class, 'toggleStatus'])->name('sections.toggle-status');
                Route::post('/students/{student}/create-account', [StudentController::class, 'createAccount'])->name('students.create-account');
        });

    Route::middleware('role:sub_admin')->prefix('sub-admin')->name('sub-admin.')->group(function () {
    Route::get('/dashboard', [SubAdminController::class, 'dashboard'])->name('dashboard');
});

    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [ParentPortalController::class, 'index'])->name('dashboard');
        Route::get('/students/{student}/dashboard', [ParentPortalController::class, 'dashboard'])->name('students.dashboard');
        Route::get('/students/{student}/attendance', [ParentPortalController::class, 'attendance'])->name('students.attendance');
        Route::get('/announcements', [ParentPortalController::class, 'announcements'])->name('announcements');
    });

    Route::middleware('role:guard')->prefix('guard')->name('guard.')->group(function () {
        Route::get('/dashboard', [GuardController::class, 'dashboard'])->name('dashboard');
    });
    
        Route::middleware('role:admin,sub_admin')->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
        Route::patch('/{announcement}/toggle-pin', [AnnouncementController::class, 'togglePin'])->name('toggle-pin');
    });

        Route::middleware('role:admin,sub_admin,guard')->prefix('attendance-corrections')->name('attendance-corrections.')->group(function () {
        Route::get('/', [AttendanceCorrectionController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceCorrectionController::class, 'create'])->name('create');
        Route::get('/create/{attendanceRecord}', [AttendanceCorrectionController::class, 'createForRecord'])->name('create-for-record');
        Route::post('/', [AttendanceCorrectionController::class, 'store'])->name('store');
    });

    Route::middleware('role:admin,sub_admin')->prefix('attendance-corrections')->name('attendance-corrections.')->group(function () {
        Route::patch('/{correction}/approve', [AttendanceCorrectionController::class, 'approve'])->name('approve');
        Route::patch('/{correction}/reject', [AttendanceCorrectionController::class, 'reject'])->name('reject');
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
        Route::get('/profile', [StudentPortalController::class, 'profile'])->name('profile');
        Route::get('/account-settings', [StudentPortalController::class, 'accountSettings'])->name('account-settings');
        Route::put('/account-settings', [StudentPortalController::class, 'updateAccount'])->name('account-settings.update');
    });
