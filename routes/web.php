<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| Hizi ni routes za kuingia na kutoka kwenye mfumo. Hazihitaji middleware maalum
| kwa sababu mtumiaji anahitaji kupata routes hizi bila ruhusa.
|
*/
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Password Reset Routes
|--------------------------------------------------------------------------
| Hizi ni routes zinazohusika na kuweka upya nenosiri. Hazihitaji middleware
| kwa kuwa mtumiaji lazima aweze kuzifikia hata kama hajaingia kwenye mfumo.
|
*/
Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

/*
|--------------------------------------------------------------------------
| Admin / HR Dashboard Routes
|--------------------------------------------------------------------------
| Hizi ni routes kwa ajili ya Admin na HR. Zimefungwa na middleware ya 'auth'
| ili kuhakikisha kuwa ni watumiaji waliothibitishwa pekee wanaozipata.
|
*/
Route::prefix('dashboard')->middleware(['auth'])->group(function () {
    // Dashboard home
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Employee Management
    Route::resource('employees', EmployeeController::class);
    Route::post('/employees/import', [EmployeeController::class, 'import'])->name('employees.bulk-import');

    // Payroll
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll');
    Route::post('/payroll/run', [PayrollController::class, 'run'])->name('payroll.run');
    Route::post('/payroll/revert', [PayrollController::class, 'revert'])->name('payroll.revert');
    Route::post('/payroll/retro', [PayrollController::class, 'retro'])->name('payroll.retro');
    Route::post('/payroll/paycheck', [PayrollController::class, 'paycheck'])->name('payroll.paycheck');
    Route::get('/payroll/transactions', [PayrollController::class, 'transactions'])->name('payroll.transactions');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
    Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
    Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');

    // Compliance
    Route::resource('compliance', ComplianceController::class);
    Route::post('/compliance/{task}/submit', [ComplianceController::class, 'submit'])->name('compliance.submit');

    // Attendance (Admin/HR)
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('dashboard.attendance');
    Route::post('/attendance/store', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings');
    Route::post('/settings/update', [SettingController::class, 'update'])->name('settings.update');

    // Roles
    Route::resource('roles', RoleController::class);
});

/*
|--------------------------------------------------------------------------
| Employee Portal Routes
|--------------------------------------------------------------------------
| Hizi ni routes kwa ajili ya wafanyakazi. Zimefungwa na middleware ya 'auth'
| ili kuhakikisha kuwa ni wafanyakazi waliothibitishwa pekee wanaozipata.
|
*/
Route::prefix('portal')->middleware(['auth'])->group(function () {
    // Employee Portal Home
    Route::get('/', [EmployeePortalController::class, 'index'])->name('employee.portal');

    // Employee Portal actions
    Route::post('/update', [EmployeePortalController::class, 'update'])->name('employee.portal.update');
    Route::get('/payslips/{id}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('employee.payslip.download');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('portal.attendance');
    Route::post('/leave/request', [AttendanceController::class, 'requestLeave'])->name('leave.request');
});