<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Setting;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class EmployeePortalController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $employee = Employee::findOrFail($user->employee_id);
        $payslips = Payslip::where('employee_id', $employee->id)->latest()->get();
        $leaveBalances = [
            'sick_leave_balance' => $employee->sick_leave_balance ?? 14,
            'vacation_leave_balance' => $employee->vacation_leave_balance ?? 28,
            'maternity_leave_balance' => $employee->maternity_leave_balance ?? 84,
        ];
        return view('dashboard.employee_portal', compact('employee', 'payslips', 'leaveBalances'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::findOrFail($user->employee_id);

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employee->update($request->all());

        return redirect()->route('employee.portal')->with('success', 'Details updated successfully. Pending approval.');
    }

    public function downloadPayslip($id)
    {
        $user = Auth::user();
        $payslip = Payslip::where('employee_id', $user->employee_id)->findOrFail($id);
        $settings = Setting::first();

        $pdf = Pdf::loadView('payslip.download', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
            'settings' => $settings,
        ]);
        return $pdf->download('payslip_' . $payslip->payroll_id . '.pdf');
    }
}