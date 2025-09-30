<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
    
    // Password Reset Routes
    Route::get('password/reset', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('password/email', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [LoginController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required - Session timeout included in web group)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Logout Route
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    
    /*
    |--------------------------------------------------------------------------
    | Shared Routes (Admin/HR/Employee)
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    /*
    |--------------------------------------------------------------------------
    | Admin / HR Dashboard Routes (Role-based Access)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,hr'])->prefix('dashboard')->group(function () {
        // Employee Routes
        // 1. Employee Routes: STATIK zenye maneno maalum (Lazima ziwe juu)
        Route::post('/employees/bulk-import', [EmployeeController::class, 'bulkImport'])->name('employees.bulk-import');
        Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');
        Route::get('/employees/download-template', [EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
        
        // 2. Route za msingi
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        
        // 3. Route za DINAMIKI zenye parameter ya {employeeId} (Lazima ziwe chini)
        Route::get('/employees/{employeeId}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::put('/employees/{employeeId}/update', [EmployeeController::class, 'update'])->name('employees.update');
        Route::put('/employees/{employeeId}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle.status');

        // Payroll Routes
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll');
        Route::post('/payroll/run', [PayrollController::class, 'run'])->name('payroll.run');
        Route::post('/payroll/retro', [PayrollController::class, 'retro'])->name('payroll.retro');
        Route::post('/payroll/revert', [PayrollController::class, 'revert'])->name('payroll.revert');
        Route::post('/payroll/revert-all', [PayrollController::class, 'revertAll'])->name('payroll.revert.all');
        Route::get('/payroll/{id}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('/payroll/transaction/{id}', [PayrollController::class, 'showTransaction'])->name('transaction.show');
        Route::get('/payroll/alert/{id}', [PayrollController::class, 'showAlert'])->name('alert.show');
        Route::post('/payroll/alert/{id}/read', [PayrollController::class, 'markAlertRead'])->name('alert.read');
        Route::post('/payroll/export/pdf', [PayrollController::class, 'exportPDF'])->name('payroll.export.pdf');
        Route::post('/payroll/export/excel', [PayrollController::class, 'exportExcel'])->name('payroll.export.excel');

        // Reports Routes
        Route::get('/reports', [ReportController::class, 'index'])->name('reports');
        Route::post('/reports/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/reports/{id}/download', [ReportController::class, 'download'])->name('reports.download');
        Route::delete('/reports/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');

        // Compliance Routes
        Route::resource('compliance', ComplianceController::class);
        Route::post('/compliance/{task}/submit', [ComplianceController::class, 'submit'])->name('compliance.submit');

        // Attendance Routes (Dashboard level)
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('dashboard.attendance');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');
        Route::post('/leave-request', [AttendanceController::class, 'requestLeave'])->name('attendance.requestLeave');
        Route::get('/leave-request/{id}/review', [AttendanceController::class, 'reviewLeaveRequest'])->name('attendance.reviewLeaveRequest');
        Route::put('/leave-request/{id}/review', [AttendanceController::class, 'updateLeaveRequest'])->name('attendance.updateLeaveRequest');

        // Settings Routes
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/personal', [SettingController::class, 'updatePersonal'])->name('settings.update');
            Route::put('/payroll', [SettingController::class, 'updatePayroll'])->name('settings.payroll.update');
            Route::put('/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications.update');
            Route::put('/integrations', [SettingController::class, 'updateIntegrations'])->name('settings.integrations.update');
            Route::post('/allowances', [SettingController::class, 'storeAllowance'])->name('settings.allowances.store');
            Route::put('/allowances/{allowance}', [SettingController::class, 'updateAllowance'])->name('settings.allowances.update');
            Route::delete('/allowances/{allowance}', [SettingController::class, 'destroyAllowance'])->name('settings.allowances.destroy');
            Route::post('/deductions', [SettingController::class, 'storeDeduction'])->name('settings.deductions.store');
            Route::put('/deductions/{deduction}', [SettingController::class, 'updateDeduction'])->name('settings.deductions.update');
            Route::delete('/deductions/{deduction}', [SettingController::class, 'destroyDeduction'])->name('settings.deductions.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Employee Portal Routes (Employee Role Only)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:employee,admin,hr'])->prefix('portal')->group(function () {
        Route::get('/', [EmployeePortalController::class, 'index'])->name('employee.portal');
        Route::post('/update', [EmployeePortalController::class, 'update'])->name('employee.portal.update');
        Route::get('/employee/reports/{id}/download', [EmployeePortalController::class, 'downloadReport'])->name('employee.report.download');
        Route::get('/payslips/{id}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('employee.payslip.download');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('portal.attendance');
        Route::post('/attendance/sync-biometric', [AttendanceController::class, 'syncBiometric'])->name('attendance.syncBiometric');
        Route::post('/leave/request', [AttendanceController::class, 'requestLeave'])->name('leave.request');
        Route::get('/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('portal.attendance.edit');
        Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('portal.attendance.update');
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});