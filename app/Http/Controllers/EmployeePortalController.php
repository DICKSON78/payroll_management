<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
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

        if (in_array($user->role, ['admin', 'hr'])) {
            // Admin/HR wanaona employees wote
            $employees = Employee::with('payslips')->get();
            $payslips = Payslip::with('employee')->latest()->take(50)->get();
        } else {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
            $employees = collect([$employee]);
            $payslips = $employee->payslips()->latest()->get();
        }

        return view('dashboard.employeeportal', compact('employees', 'payslips'));
    }

    /**
     * Update employee information
     */
    public function update(Request $request, $id = null)
    {
        $user = Auth::user();

        if ($user->role === 'employee') {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
        } else {
            $employee = Employee::findOrFail($id);
        }

        $validator = Validator::make($request->all(), [
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'account_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employee->update($request->only(['phone','address','bank_name','account_number']));

        return redirect()->route('employee.portal')->with('success', 'Details updated successfully.');
    }

    /**
     * Download payslip PDF
     */
    public function downloadPayslip($id)
    {
        $user = Auth::user();
        $payslip = Payslip::with('employee')->findOrFail($id);

        // Employee wa kawaida hawawezi download payslip za wengine
        if ($user->role === 'employee' && $payslip->employee->user_id !== $user->id) {
            return redirect()->back()->with('error', 'Unauthorized to download this payslip.');
        }

        $pdf = Pdf::loadView('payslip.download', [
            'payslip' => $payslip,
            'employee' => $payslip->employee,
        ]);

        return $pdf->download('payslip_' . $payslip->employee->employee_id . '_' . $payslip->period . '.pdf');
    }
}
