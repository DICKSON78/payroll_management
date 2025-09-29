<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Report;
use App\Models\LeaveRequest;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
                ->where('employee_id', $employee->id)
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
        $usedDays = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type', $leaveType)
            ->where('status', 'Approved')
            ->whereYear('start_date', Carbon::now()->year)
            ->sum('days');

        return max(0, ($maxDays[$leaveType] ?? 0) - $usedDays);
    }

    /**
     * Update employee information
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // Check if user has valid role
        if (!in_array(strtolower($user->role), ['admin', 'hr manager', 'employee', 'manager'])) {
            return redirect()->back()->with('error', 'Access denied.');
        }

        $employee = $user;

        // Validation rules
        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|exists:banks,name',
            'account_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update employee details
        $employee->update($request->only([
            'phone',
            'address',
            'bank_name',
            'account_number',
        ]));

        return redirect()->route('employee.portal')->with('success', 'Your details have been updated successfully.');
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
            'employee_id' => $employee->id,
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

        $payslip = Payslip::with('employee')->findOrFail($id);

        // Allow admin/hr to download any payslip, employees only their own
        if (!in_array(strtolower($user->role), ['admin', 'hr manager']) && $payslip->employee_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized to download this payslip.');
        }

        $pdf = Pdf::loadView('reports.payslip', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
            'settings' => [
                'company_name' => 'Your Company',
                'currency' => 'TZS',
            ]
        ]);

        return $pdf->download('payslip_' . $payslip->employee->employee_id . '_' . $payslip->period . '.pdf');
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
            if ($report->employee_id !== $employee->id || !in_array($report->type, self::EMPLOYEE_ALLOWED_REPORTS)) {
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
                ->where('employee_id', $employee->id)
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