<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\SettingController;

//Authentication Route
Route::get('/', [LoginController::class, 'showLoginForm'])->name('login');

//Registration Route
Route::get('/register', [RegisterController::class, 'showRegisterForm'])->name('register');


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard')->group(function () {
        // Employee Routes
        Route::get('/employee', [EmployeeController::class, 'index'])->name('employees');
        Route::post('/employee', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employee/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employee/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::post('/employee/import', [EmployeeController::class, 'import'])->name('employees.import');

        // Payroll Routes
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll');
        Route::post('/payroll/run', [PayrollController::class, 'run'])->name('payroll.run');
        Route::post('/payroll/retro', [PayrollController::class, 'retro'])->name('payroll.retro');
        Route::post('/payroll/paycheck', [PayrollController::class, 'paycheck'])->name('payroll.paycheck');
        Route::get('/payroll/transactions', [PayrollController::class, 'transactions'])->name('payroll.transactions');

        // Report Routes
        Route::get('/report', [ReportController::class, 'index'])->name('reports');
        Route::post('/report/generate', [ReportController::class, 'generate'])->name('reports.generate');
        Route::get('/report/{id}/{format}', [ReportController::class, 'download'])->name('reports.download');
        Route::post('/report/tax-forms', [ReportController::class, 'taxForms'])->name('reports.tax_forms');

        // Compliance Routes
        Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance');
        Route::post('/compliance', [ComplianceController::class, 'store'])->name('compliance.store');
        Route::get('/compliance/{id}/edit', [ComplianceController::class, 'edit'])->name('compliance.edit');
        Route::put('/compliance/{id}', [ComplianceController::class, 'update'])->name('compliance.update');

        // Settings Routes
        Route::get('/setting', [SettingController::class, 'index'])->name('settings');
        Route::post('/setting', [SettingController::class, 'update'])->name('settings.update');

        // Attendance Routes
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/{id}/edit', [AttendanceController::class, 'edit'])->name('attendance.edit');
        Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
        Route::post('/attendance/leave', [AttendanceController::class, 'requestLeave'])->name('leave.request');
        Route::post('/attendance/export', [AttendanceController::class, 'export'])->name('attendance.export');

        // Employee Portal Routes
        Route::get('/employee-portal', [EmployeePortalController::class, 'index'])->name('employee.portal');
        Route::put('/employee-portal', [EmployeePortalController::class, 'update'])->name('employee.update');
        Route::get('/payslip/{id}/download', [EmployeePortalController::class, 'downloadPayslip'])->name('payslip.download');
         });
});
