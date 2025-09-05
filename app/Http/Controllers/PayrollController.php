<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\Payslip;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayslipsExport;

class PayrollController extends Controller
{
   public function index()
    {
        $payrolls = Payroll::all();
        $departments = Employee::distinct()->pluck('department');
        $settings = Setting::first() ?? ['company_name' => 'Default Company', 'currency' => 'TZS', 'tax_rate' => 30.00, 'payroll_cycle' => 'Monthly', 'company_logo' => null];
        return view('dashboard.payroll', compact('payrolls', 'departments', 'settings'));
    }
    public function run(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payroll_period' => 'required|date_format:Y-m',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'nssf_rate' => 'required|numeric|min:0|max:100',
            'nhif_rate' => 'required|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $employees = Employee::whereIn('id', $request->employee_ids)->get();
        $period = $request->payroll_period;
        $nssf_rate = $request->nssf_rate / 100;
        $nhif_rate = $request->nhif_rate;
        $wcf_rate = 0.005; // 0.5%
        $sdl_rate = 0.035; // 3.5%

        foreach ($employees as $employee) {
            $gross_salary = $employee->base_salary + ($employee->allowances ?? 0);
            $taxable = $gross_salary * (1 - $nssf_rate);
            $paye = $this->calculatePAYE($taxable); // Implement PAYE logic
            $nssf = $gross_salary * $nssf_rate;
            $wcf = $gross_salary * $wcf_rate;
            $sdl = $gross_salary * $sdl_rate;
            $net_salary = $gross_salary - ($nssf + $paye + $nhif_rate + ($employee->other_deductions ?? 0));

            Payslip::create([
                'employee_id' => $employee->id,
                'payroll_id' => 'PAY-' . uniqid(),
                'period' => $period,
                'gross_salary' => $gross_salary,
                'nssf' => $nssf,
                'paye' => $paye,
                'nhif' => $nhif_rate,
                'other_deductions' => $employee->other_deductions ?? 0,
                'net_salary' => $net_salary,
                'wcf' => $wcf,
                'sdl' => $sdl,
                'status' => 'Processed'
            ]);
        }

        return redirect()->route('dashboard.payroll')->with('success', 'Payroll processed successfully');
    }

    private function calculatePAYE($taxable)
    {
        // Tanzanian PAYE tax brackets (as per 2023 rates, adjust as needed)
        if ($taxable <= 270000) return 0;
        if ($taxable <= 520000) return ($taxable - 270000) * 0.08;
        if ($taxable <= 760000) return 20000 + ($taxable - 520000) * 0.20;
        if ($taxable <= 1000000) return 68000 + ($taxable - 760000) * 0.25;
        return 118000 + ($taxable - 1000000) * 0.30;
    }
}
