<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Transaction;
use App\Models\PayrollAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display payroll dashboard based on role
     */
    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'hr'])) {
            $payrolls = Payroll::paginate(10);
            $payroll_alerts = PayrollAlert::with('employee')->where('status', 'Pending')->get();
            $employees = Employee::select('id', 'name', 'department')->get();
            $transactions = Transaction::with('employee')->paginate(10);
        } else {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
            $payrolls = Payslip::where('employee_id', $employee->id)
                ->with('payroll')
                ->get()
                ->pluck('payroll')
                ->unique('id')
                ->take(10);
            $payroll_alerts = collect();
            $employees = collect([$employee]);
            $transactions = Transaction::where('employee_id', $employee->id)->paginate(10);
        }

        $departments = Employee::distinct()->pluck('department');
        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        return view('dashboard.payroll', compact('payrolls', 'departments', 'settings', 'payroll_alerts', 'employees', 'transactions'));
    }

    /**
     * Run payroll - Admin and HR only
     */
    public function run(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can run payroll.');
        }

        $validator = Validator::make($request->all(), [
            'payroll_period' => 'required|date_format:Y-m',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'nssf_rate' => 'required|numeric|min:0|max:100',
            'nhif_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $period = Carbon::createFromFormat('Y-m', $request->payroll_period)->startOfMonth();
        $employees = $request->employee_ids[0] === 'all' 
            ? Employee::all() 
            : Employee::whereIn('id', $request->employee_ids)->get();

        foreach ($employees as $employee) {
            $baseSalary = $employee->base_salary;
            $allowances = $employee->allowances ?? 0;
            $housingAllowance = $this->calculateHousingAllowance($employee);
            $transportAllowance = $this->calculateTransportAllowance($employee);
            $medicalAllowance = $this->calculateMedicalAllowance($employee);

            $totalAllowances = $allowances + $housingAllowance + $transportAllowance + $medicalAllowance;
            $grossSalary = $baseSalary + $totalAllowances;

            $paye = $this->calculatePAYE($grossSalary);
            $nssf = $this->calculateNSSF($baseSalary, $request->nssf_rate / 100);
            $nhif = $this->calculateNHIF($grossSalary, $request->nhif_rate / 100);
            $wcf = $this->calculateWCF($baseSalary);
            $sdl = $this->calculateSDL($baseSalary);
            $tuico = $this->calculateTUICO($baseSalary);
            $heslb = $this->calculateHESLB($baseSalary);

            $totalDeductions = $paye + $nssf + $nhif + $wcf + $sdl + $tuico + $heslb;
            $netSalary = $grossSalary - $totalDeductions;

            Payslip::create([
                'employee_id' => $employee->id,
                'period' => $period,
                'base_salary' => $baseSalary,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'medical_allowance' => $medicalAllowance,
                'gross_salary' => $grossSalary,
                'paye' => $paye,
                'nssf' => $nssf,
                'nhif' => $nhif,
                'wcf' => $wcf,
                'sdl' => $sdl,
                'tuico' => $tuico,
                'heslb' => $heslb,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ]);
        }

        Payroll::create([
            'payroll_id' => 'PR-' . date('Ym') . '-' . str_pad(Payroll::count() + 1, 4, '0', STR_PAD_LEFT),
            'period' => $period,
            'total_amount' => Payslip::where('period', $period)->sum('net_salary'),
            'status' => 'Completed',
            'processed_by' => Auth::id(),
        ]);

        return redirect()->route('payroll')->with('success', 'Payroll run completed successfully!');
    }

    /**
     * Process retroactive pay - Admin and HR only
     */
    public function retro(Request $request)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can process retroactive pay.');
        }

        $validator = Validator::make($request->all(), [
            'retro_period' => 'required|date_format:Y-m',
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $period = Carbon::createFromFormat('Y-m', $request->retro_period)->startOfMonth();
        $employees = $request->employee_ids[0] === 'all' 
            ? Employee::all() 
            : Employee::whereIn('id', $request->employee_ids)->get();

        foreach ($employees as $employee) {
            $originalPayslip = Payslip::where('employee_id', $employee->id)
                ->where('period', $period)
                ->first();

            if ($originalPayslip) {
                $amount = $originalPayslip->net_salary * 0.1; // Example: 10% of original net salary
                Transaction::create([
                    'employee_id' => $employee->id,
                    'type' => 'Retroactive Pay',
                    'amount' => $amount,
                    'description' => "Retroactive pay adjustment for period {$request->retro_period}",
                    'status' => 'Processed',
                    'transaction_date' => now(),
                ]);
            }
        }

        return redirect()->route('payroll')->with('success', 'Retroactive pay processed successfully');
    }

    /**
     * Generate paycheck PDF
     */
    public function paycheck(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|exists:employees,id',
            'period' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $employee = Employee::findOrFail($request->employee_id);

        if ($user->role === 'employee' && $user->id !== $employee->user_id) {
            return redirect()->back()->with('error', 'Unauthorized to access this paycheck.');
        }

        $payslip = Payslip::where('employee_id', $employee->id)
            ->where('period', $request->period)
            ->firstOrFail();
        $settings = Setting::first();

        $pdf = Pdf::loadView('payroll.paycheck', [
            'payslip' => $payslip,
            'employee' => $employee,
            'settings' => $settings,
        ]);

        return $pdf->download('paycheck_' . $employee->employee_id . '_' . $request->period . '.pdf');
    }

    /**
     * Payroll transactions view
     */
    public function transactions()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'hr'])) {
            $payrolls = Payroll::paginate(10);
            $payroll_alerts = PayrollAlert::with('employee')->where('status', 'Pending')->get();
            $employees = Employee::select('id', 'name', 'department')->get();
            $transactions = Transaction::with('employee')->paginate(10);
        } else {
            $employee = Employee::where('user_id', $user->id)->firstOrFail();
            $payrolls = Payslip::where('employee_id', $employee->id)
                ->with('payroll')
                ->get()
                ->pluck('payroll')
                ->unique('id')
                ->take(10);
            $payroll_alerts = collect();
            $employees = collect([$employee]);
            $transactions = Transaction::where('employee_id', $employee->id)->paginate(10);
        }

        $departments = Employee::distinct()->pluck('department');
        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        return view('dashboard.payroll', compact('payrolls', 'departments', 'settings', 'payroll_alerts', 'employees', 'transactions'));
    }

    /**
     * Revert last payroll - Admin and HR only
     */
    public function revert()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can revert payroll.');
        }

        $lastPayroll = Payroll::latest('period')->first();
        if (!$lastPayroll) {
            return redirect()->route('payroll')->with('error', 'No payroll to revert.');
        }

        Payslip::where('period', $lastPayroll->period)->delete();
        Transaction::where('type', 'Payroll')->where('transaction_date', $lastPayroll->period)->delete();
        $lastPayroll->delete();

        return redirect()->route('payroll')->with('success', 'Last payroll has been reverted successfully.');
    }

    // === Calculation helpers ===

    private function calculatePAYE($grossSalary)
    {
        $taxableIncome = $grossSalary * 12;
        $monthlyTax = 0;

        if ($taxableIncome <= 270000) $monthlyTax = 0;
        elseif ($taxableIncome <= 520000) $monthlyTax = ($taxableIncome - 270000) * 0.08;
        elseif ($taxableIncome <= 760000) $monthlyTax = 20000 + ($taxableIncome - 520000) * 0.20;
        elseif ($taxableIncome <= 1000000) $monthlyTax = 68000 + ($taxableIncome - 760000) * 0.25;
        else $monthlyTax = 128000 + ($taxableIncome - 1000000) * 0.30;

        return round($monthlyTax / 12, 2);
    }

    private function calculateNSSF($baseSalary, $rate = 0.10)
    {
        $nssf = $baseSalary * $rate;
        return min($nssf, 20000);
    }

    private function calculateNHIF($grossSalary, $rate = 0.03)
    {
        $nhif = $grossSalary * $rate;
        return min($nhif, 1000);
    }

    private function calculateWCF($baseSalary)
    {
        return round($baseSalary * 0.005, 2);
    }

    private function calculateSDL($baseSalary)
    {
        return round($baseSalary * 0.06, 2);
    }

    private function calculateTUICO($baseSalary)
    {
        return round($baseSalary * 0.001, 2);
    }

    private function calculateHESLB($baseSalary)
    {
        return 0;
    }

    private function calculateHousingAllowance($employee)
    {
        $seniorPositions = ['manager', 'director', 'head'];
        $position = strtolower($employee->position);
        foreach ($seniorPositions as $senior) {
            if (strpos($position, $senior) !== false) {
                return round($employee->base_salary * 0.30, 2);
            }
        }
        return round($employee->base_salary * 0.15, 2);
    }

    private function calculateTransportAllowance($employee)
    {
        if ($employee->base_salary > 1000000) return 150000;
        if ($employee->base_salary > 500000) return 100000;
        return 50000;
    }

    private function calculateMedicalAllowance($employee)
    {
        return round($employee->base_salary * 0.05, 2);
    }
}
