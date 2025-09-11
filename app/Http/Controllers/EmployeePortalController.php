<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\LeaveRequest;
use App\Models\Bank; // Import the Bank model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeePortalController extends Controller
{
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
        $employee = $user->employee()->first();
        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for this user.');
        }

        $payslips = $employee->payslips()->latest()->paginate(10);
        $leaveRequests = LeaveRequest::where('employee_id', $employee->id)->latest()->paginate(10);
        $leaveBalances = [
            'sick_leave_balance' => $employee->sick_leave_balance ?? 0,
            'vacation_leave_balance' => $employee->vacation_leave_balance ?? 0,
            'maternity_leave_balance' => $employee->maternity_leave_balance ?? 0,
        ];

        // Fetch all banks from the database
        $banks = Bank::all();

        return view('dashboard.employeeportal', compact('employee', 'payslips', 'leaveBalances', 'leaveRequests', 'banks'));
    }

    /**
     * Update employee information
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee()->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for this user.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'address' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:Male,Female,Other',
            'marital_status' => 'nullable|string|in:Single,Married,Divorced,Widowed',
            'nationality' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Update employee details
        $employee->update($request->only([
            'name',
            'phone_number',
            'address',
            'dob',
            'gender',
            'marital_status',
            'nationality',
            'bank_name',
            'bank_account_number',
        ]));

        // Update user email if it has changed
        if ($user->email !== $request->email) {
            $user->email = $request->email;
            $user->save();
        }

        return redirect()->route('employee.portal')->with('success', 'Your details have been updated successfully.');
    }

    /**
     * Handle leave request
     */
    public function leaveRequest(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee()->first();

        if (!$employee) {
            return redirect()->back()->with('error', 'No employee record found for this user.');
        }

        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'leave_type' => 'required|string',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->employee_id != $employee->id) {
            return redirect()->back()->with('error', 'Unauthorized to submit leave request for this employee.');
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type' => $request->leave_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
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
        $payslip = Payslip::with('employee')->findOrFail($id);

        // Allow admin/hr to download any payslip, employees only their own
        if ($user->role === 'employee' && $payslip->employee->email !== $user->email) {
            return redirect()->back()->with('error', 'Unauthorized to download this payslip.');
        }

        $pdf = Pdf::loadView('dashboard.payslip.download', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
        ]);

        return $pdf->download('payslip_' . $payslip->employee->employee_id . '_' . $payslip->period . '.pdf');
    }
}