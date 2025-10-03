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
| These routes handle authentication and password management for all users.
*/
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/', [LoginController::class, 'login']);
    
    // Password Reset Routes
    Route::get('/forgot-password', [LoginController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [LoginController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [LoginController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [LoginController::class, 'reset'])->name('password.update');
    
    // Test Email Route (MUST be commented out or removed before production deployment)
    // Route::get('/test-email', [LoginController::class, 'testEmail'])->name('test.email');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Authentication Required)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    
    // Logout Route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    /*
    |--------------------------------------------------------------------------
    | Shared Routes (Admin/HR/Employee)
    |--------------------------------------------------------------------------
    | Accessible by all authenticated users.
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'getDashboardData'])->name('dashboard.data');

    /*
    |--------------------------------------------------------------------------
    | Admin / HR Dashboard Routes
    |--------------------------------------------------------------------------
    | Restricted to users with 'admin' or 'hr' role.
    */
    Route::middleware(['role:admin,hr'])->prefix('admin')->group(function () {
        // Quick Actions Route (e.g., triggering payroll)
        Route::post('/quick-actions', [DashboardController::class, 'quickActions'])->name('dashboard.quick-actions');
        
        // Employee Management Routes
        Route::prefix('employees')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
            Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
            Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
            Route::get('/{employeeId}', [EmployeeController::class, 'show'])->name('employees.show');
            Route::put('/{employeeId}/update', [EmployeeController::class, 'update'])->name('employees.update');
            Route::put('/{employeeId}/toggle-status', [EmployeeController::class, 'toggleStatus'])->name('employees.toggle.status');
            Route::post('/bulk-import', [EmployeeController::class, 'bulkImport'])->name('employees.bulk-import');
            Route::get('/export', [EmployeeController::class, 'export'])->name('employees.export');
            Route::get('/download-template', [EmployeeController::class, 'downloadTemplate'])->name('employees.download-template');
        });

        // Payroll Management Routes
        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index'])->name('payroll');
            Route::post('/run', [PayrollController::class, 'run'])->name('payroll.run');
            Route::post('/retro', [PayrollController::class, 'retro'])->name('payroll.retro');
            Route::post('/revert', [PayrollController::class, 'revert'])->name('payroll.revert');
            Route::post('/revert-all', [PayrollController::class, 'revertAll'])->name('payroll.revert.all');
            Route::get('/{id}', [PayrollController::class, 'show'])->name('payroll.show');
            Route::get('/transaction/{id}', [PayrollController::class, 'showTransaction'])->name('transaction.show');
            Route::get('/alert/{id}', [PayrollController::class, 'showAlert'])->name('alert.show');
            Route::post('/alert/{id}/read', [PayrollController::class, 'markAlertRead'])->name('alert.read');
            Route::post('/export/pdf', [PayrollController::class, 'exportPDF'])->name('payroll.export.pdf');
            Route::post('/export/excel', [PayrollController::class, 'exportExcel'])->name('payroll.export.excel');
        });

        // Reporting Routes
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('reports');
            Route::post('/generate', [ReportController::class, 'generate'])->name('reports.generate');
            Route::get('/{id}/download', [ReportController::class, 'download'])->name('reports.download');
            Route::delete('/{id}', [ReportController::class, 'destroy'])->name('reports.destroy');
            Route::delete('/reports/bulk-delete', [ReportController::class, 'bulkDelete'])->name('reports.bulk-delete');
        });

        // Compliance Management Routes
        Route::prefix('compliance')->group(function () {
            Route::get('/', [ComplianceController::class, 'index'])->name('compliance.index');
            Route::get('/create', [ComplianceController::class, 'create'])->name('compliance.create');
            Route::post('/', [ComplianceController::class, 'store'])->name('compliance.store');
            Route::get('/{task}', [ComplianceController::class, 'show'])->name('compliance.show');
            Route::get('/{task}/edit', [ComplianceController::class, 'edit'])->name('compliance.edit');
            Route::put('/{task}', [ComplianceController::class, 'update'])->name('compliance.update');
            Route::delete('/{task}', [ComplianceController::class, 'destroy'])->name('compliance.destroy');
            Route::post('/{task}/submit', [ComplianceController::class, 'submit'])->name('compliance.submit');
        });

        // Attendance Tracking and Review Routes
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('dashboard.attendance');
            Route::post('/', [AttendanceController::class, 'store'])->name('attendance.store');
            Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
            Route::put('/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
            Route::post('/export', [AttendanceController::class, 'export'])->name('attendance.export');
            // Leave Request Management
            Route::post('/leave-request', [AttendanceController::class, 'requestLeave'])->name('attendance.requestLeave');
            Route::get('/leave-request/{id}/review', [AttendanceController::class, 'reviewLeaveRequest'])->name('attendance.reviewLeaveRequest');
            Route::put('/leave-request/{id}/review', [AttendanceController::class, 'updateLeaveRequest'])->name('attendance.updateLeaveRequest');
        });

        // System Settings Routes
        Route::prefix('settings')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('settings.index');
            Route::put('/payroll', [SettingController::class, 'updatePayroll'])->name('settings.payroll.update');
            Route::put('/notifications', [SettingController::class, 'updateNotifications'])->name('settings.notifications.update');
            Route::put('/integrations', [SettingController::class, 'updateIntegrations'])->name('settings.integrations.update');
            
            // Allowances CRUD
            Route::post('/allowances', [SettingController::class, 'storeAllowance'])->name('settings.allowances.store');
            Route::put('/allowances/{allowance}', [SettingController::class, 'updateAllowance'])->name('settings.allowances.update');
            Route::delete('/allowances/{allowance}', [SettingController::class, 'destroyAllowance'])->name('settings.allowances.destroy');
            
            // Deductions CRUD
            Route::post('/deductions', [SettingController::class, 'storeDeduction'])->name('settings.deductions.store');
            Route::put('/deductions/{deduction}', [SettingController::class, 'updateDeduction'])->name('settings.deductions.update');
            Route::delete('/deductions/{deduction}', [SettingController::class, 'destroyDeduction'])->name('settings.deductions.destroy');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Employee Portal Routes
    |--------------------------------------------------------------------------
    | Accessible by 'employee', 'admin', and 'hr' roles.
    */
    Route::middleware(['role:employee,admin,hr'])->prefix('portal')->group(function () {
        Route::get('/', [EmployeePortalController::class, 'index'])->name('employee.portal');
        Route::post('/update', [EmployeePortalController::class, 'update'])->name('employee.portal.update');
        Route::post('/security', [EmployeePortalController::class, 'updateSecurity'])->name('employee.portal.security');
        Route::post('/leave-request', [EmployeePortalController::class, 'leaveRequest'])->name('employee.portal.leave.request');
        Route::get('/reports/{id}/download', [EmployeePortalController::class, 'downloadReport'])->name('employee.portal.download.report');
        Route::get('/payslips/{id}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('employee.portal.download.payslip');
        Route::get('/reports', [EmployeePortalController::class, 'getEmployeeReports'])->name('employee.portal.reports');
        Route::get('/payslips', [EmployeePortalController::class, 'getEmployeePayslips'])->name('employee.portal.payslips');
        
        // Portal Attendance Routes
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('portal.attendance');
            Route::post('/sync-biometric', [AttendanceController::class, 'syncBiometric'])->name('attendance.syncBiometric');
            Route::post('/leave/request', [AttendanceController::class, 'requestLeave'])->name('leave.request');
            Route::get('/{id}/edit', [AttendanceController::class, 'edit'])->name('portal.attendance.edit');
            Route::put('/{id}', [AttendanceController::class, 'update'])->name('portal.attendance.update');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
| Redirects authenticated users to the dashboard and guests to the login page.
*/
Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});