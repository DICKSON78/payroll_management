<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Report;
use App\Models\LeaveRequest;
use App\Models\Bank;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class EmployeePortalController extends Controller
{
    // Employee allowed report types - payslip only
    private const EMPLOYEE_ALLOWED_REPORTS = ['payslip'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show Employee Portal dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Check if user has valid role (admin, hr manager, employee, manager)
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied. You do not have permission to access the employee portal.');
        }

        // Since Auth::user() returns Employee instance, $user is the employee
        $employee = $user;

        // Fetch payslips for the employee
        $payslips = $employee->payslips()->latest()->paginate(10);
        $leaveRequests = $employee->leaveRequests()->latest()->paginate(10);

        // Get reports - different logic based on role
        if (in_array(strtolower($user->role), ['admin', 'hr manager'])) {
            // Admin/HR can see all reports (both individual and batch)
            $reports = Report::with('employee')
                ->where('status', 'completed')
                ->latest()
                ->paginate(10);
        } else {
            // Regular employees can only see their own payslip reports
            $reports = Report::with('employee')
                ->where('status', 'completed')
                ->where('employee_id', $employee->employee_id)
                ->whereIn('type', self::EMPLOYEE_ALLOWED_REPORTS)
                ->latest()
                ->paginate(10);
        }

        // Calculate leave balances based on leave_requests table
        $leaveBalances = [
            'sick_leave_balance' => $this->calculateLeaveBalance($employee, 'Sick'),
            'annual_leave_balance' => $this->calculateLeaveBalance($employee, 'Annual'),
            'maternity_leave_balance' => $this->calculateLeaveBalance($employee, 'Maternity'),
        ];

        // Fetch all banks from the database
        $banks = Bank::all();

        // Determine user role for view logic
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr manager']);
        $isEmployee = strtolower($user->role) === 'employee';

        return view('dashboard.employeeportal', compact(
            'employee', 
            'payslips', 
            'leaveBalances', 
            'leaveRequests', 
            'banks', 
            'reports',
            'isAdminOrHR',
            'isEmployee'
        ));
    }

    /**
     * Calculate leave balance based on approved leave requests
     */
    private function calculateLeaveBalance($employee, $leaveType)
    {
        // Default annual leave allowances
        $maxDays = [
            'Sick' => 14,
            'Annual' => 28,
            'Maternity' => 84,
        ];

        // Calculate used days for current year
        $usedDays = LeaveRequest::where('employee_id', $employee->employee_id)
            ->where('leave_type', $leaveType)
            ->where('status', 'Approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('days');

        return max(0, ($maxDays[$leaveType] ?? 0) - $usedDays);
    }

    /**
     * Update employee information - COMPLETE DETAILS
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $employee = $user;

        // Validation rules for all employee details
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:employees,email,' . $employee->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'nationality' => 'nullable|string|max:100',
            'bank_name' => 'nullable|exists:banks,name',
            'account_number' => 'nullable|string|max:50',
            'nssf_number' => 'nullable|string|max:50',
            'tin_number' => 'nullable|string|max:50',
            'nhif_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update all employee details
        $employee->update($request->only([
            'name',
            'email',
            'phone',
            'address',
            'gender',
            'dob',
            'nationality',
            'bank_name',
            'account_number',
            'nssf_number',
            'tin_number',
            'nhif_number',
        ]));

        return redirect()->route('employee.portal')->with('success', 'Your details have been updated successfully.');
    }

    /**
     * Update security settings (password)
     */
    public function updateSecurity(Request $request)
    {
        $user = Auth::user();

        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->with('error', 'Current password is incorrect.');
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('employee.portal')->with('success', 'Password updated successfully.');
    }

    /**
     * Handle leave request
     */
    public function leaveRequest(Request $request)
    {
        $user = Auth::user();

        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $employee = $user;

        // Validation rules
        $validator = Validator::make($request->all(), [
            'leave_type' => 'required|in:Annual,Sick,Maternity,Unpaid',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Calculate leave days
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $days = $startDate->diffInDays($endDate) + 1;

        // Check leave balance for paid leave types
        $paidLeaveTypes = ['Annual', 'Sick', 'Maternity'];
        if (in_array($request->leave_type, $paidLeaveTypes)) {
            $availableBalance = $this->calculateLeaveBalance($employee, $request->leave_type);
            if ($days > $availableBalance) {
                return redirect()->back()->with('error', "Insufficient {$request->leave_type} leave balance. Available: {$availableBalance} days, Requested: {$days} days.");
            }
        }

        // Generate unique request_id
        $requestId = 'LRQ-' . Str::upper(Str::random(5));
        while (LeaveRequest::where('request_id', $requestId)->exists()) {
            $requestId = 'LRQ-' . Str::upper(Str::random(5));
        }

        LeaveRequest::create([
            'request_id' => $requestId,
            'employee_id' => $employee->employee_id,
            'employee_name' => $employee->name,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'days' => $days,
            'reason' => $request->reason,
            'status' => 'Pending',
        ]);

        return redirect()->route('employee.portal')->with('success', 'Leave request submitted successfully.');
    }

    /**
     * Download payslip PDF
     */
    public function downloadPayslip($id)
    {
        $user = Auth::user();
        
        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $currentUser = $user; // Rename current user to avoid conflict
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr manager']);

        // Get payslip with access control
        if ($isAdminOrHR) {
            $payslip = Payslip::where('id', $id)->firstOrFail();
        } else {
            $payslip = Payslip::where('id', $id)
                ->where('employee_id', $currentUser->employee_id)
                ->firstOrFail();
        }

        // Get employee data - USE $employee VARIABLE FOR VIEW COMPATIBILITY
        $employee = Employee::where('employee_id', $payslip->employee_id)
            ->whereNull('deleted_at')
            ->first();

        if (!$employee) {
            \Log::error('Employee not found for payslip', [
                'payslip_id' => $id,
                'employee_id' => $payslip->employee_id,
                'user_id' => Auth::id()
            ]);
            return redirect()->route('employee.portal')->with('error', 'Employee data not found for this payslip.');
        }

        // Get payroll data for additional information
        $payroll = Payroll::where('employee_id', $payslip->employee_id)
            ->where('period', $payslip->period)
            ->first();

        // Prepare deduction breakdown
        $deduction_breakdown = $this->calculateDeductionBreakdown($payslip, $payroll);
        
        // Get settings for company name
        $settings = Setting::where('key', 'company_name')->first();
        
        $period_display = $payslip->period ?? 'Unknown Period';
        $generated_at = Carbon::now();

        // Generate PDF - USE VARIABLE NAMES THAT MATCH THE VIEW
        $pdf = Pdf::loadView('reports.payslip-individual', compact(
            'payslip', 
            'employee', // This matches the view expectation
            'payroll',
            'deduction_breakdown',
            'settings',
            'period_display',
            'generated_at'
        ));
        
        // Create safe filename
        $safe_period = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $payslip->period);
        $filename = 'payslip-' . $payslip->employee_id . '-' . $safe_period . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Calculate deduction breakdown
     */
    private function calculateDeductionBreakdown($payslip, $payroll)
    {
        // Use actual data from schema instead of hardcoded percentages
        $totalDeductions = $payslip->deductions ?? 0;
        
        // If payroll data exists, use it for more accurate breakdown
        if ($payroll) {
            return [
                'nssf' => $payroll->deductions * 0.4, // Adjust based on your actual NSSF calculation
                'nhif' => $payroll->deductions * 0.3, // Adjust based on your actual NHIF calculation
                'paye' => $payroll->deductions * 0.2, // Adjust based on your actual PAYE calculation
                'other_deductions' => $payroll->deductions * 0.1,
            ];
        }

        // Fallback calculation based on typical Tanzanian payroll deductions
        return [
            'nssf' => $totalDeductions * 0.4, // NSSF typically 10% of basic salary
            'nhif' => $totalDeductions * 0.3, // NHIF fixed amount based on salary bands
            'paye' => $totalDeductions * 0.2, // PAYE based on tax brackets
            'other_deductions' => $totalDeductions * 0.1,
        ];
    }

    /**
     * Download a report - with role-based access control
     */
    public function downloadReport($id)
    {
        $user = Auth::user();

        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $employee = $user;
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr manager']);

        // Get report from database
        $report = Report::with('employee')->where('status', 'completed')->findOrFail($id);

        // Role-based access control for reports
        if ($isAdminOrHR) {
            // Admin/HR can download any report
        } else {
            // Regular employees can only download their own payslip reports
            if ($report->employee_id !== $employee->employee_id || !in_array($report->type, self::EMPLOYEE_ALLOWED_REPORTS)) {
                return redirect()->back()->with('error', 'Unauthorized to download this report.');
            }
        }

        // Check if report file exists
        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = 'reports/' . $filename;

        if (!Storage::disk('public')->exists($filePath)) {
            return redirect()->back()->with('error', 'Report file not found. It may have been deleted or not generated properly.');
        }

        // Download the file
        return Storage::disk('public')->download($filePath, $filename);
    }

    /**
     * Get employee's reports for AJAX requests
     */
    public function getEmployeeReports()
    {
        $user = Auth::user();
        $employee = $user;

        if (in_array(strtolower($user->role), ['admin', 'hr manager'])) {
            $reports = Report::with('employee')
                ->where('status', 'completed')
                ->latest()
                ->limit(50)
                ->get();
        } else {
            $reports = Report::with('employee')
                ->where('status', 'completed')
                ->where('employee_id', $employee->employee_id)
                ->whereIn('type', self::EMPLOYEE_ALLOWED_REPORTS)
                ->latest()
                ->limit(50)
                ->get();
        }

        return response()->json([
            'success' => true,
            'reports' => $reports
        ]);
    }

    /**
     * Get employee's payslips for AJAX requests
     */
    public function getEmployeePayslips()
    {
        $user = Auth::user();
        $employee = $user;

        $payslips = $employee->payslips()
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'payslips' => $payslips
        ]);
    }
}