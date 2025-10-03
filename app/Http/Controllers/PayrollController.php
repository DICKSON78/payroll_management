<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Setting;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Transaction;
use App\Models\PayrollAlert;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\RetroactiveAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display payroll dashboard based on role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $unread_alerts_count = PayrollAlert::where('status', 'unread')->count();

        // PAYROLLS - With all necessary employee data
        $payrolls = Payroll::select(
                'payrolls.*',
                'employees.department',
                'employees.position',
                'employees.email',
                'employees.phone',
                'employees.bank_name',
                'employees.account_number'
            )
            ->leftJoin('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->orderBy('payrolls.created_at', 'desc')
            ->paginate(10);

        // PAYROLL ALERTS - With all necessary employee data
        $payroll_alerts = PayrollAlert::select(
                'payroll_alerts.*',
                'employees.department',
                'employees.position',
                'employees.email',
                'employees.name as employee_name'
            )
            ->leftJoin('employees', 'payroll_alerts.employee_id', '=', 'employees.employee_id')
            ->whereIn('payroll_alerts.status', ['Unread', 'Read'])
            ->orderBy('payroll_alerts.created_at', 'desc')
            ->paginate(10);

        // EMPLOYEES - All active employees with complete data
        $employees = Employee::where('status', 'active')
            ->select(
                'employee_id',
                'name',
                'email',
                'department',
                'position',
                'base_salary',
                'bank_name',
                'account_number',
                'phone',
                'status'
            )
            ->get();

        // TRANSACTIONS - With all necessary employee data
        $transactions = Transaction::select(
                'transactions.*',
                'employees.department',
                'employees.position',
                'employees.email'
            )
            ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.employee_id')
            ->orderBy('transactions.transaction_date', 'desc')
            ->paginate(10);

        $departments = Employee::distinct()->pluck('department');
        $settings = $this->getSettings();
        $allowances = Allowance::where('active', 1)->whereNull('deleted_at')->get();
        $deductions = Deduction::where('active', 1)->whereNull('deleted_at')->get();
        $activeTab = $request->query('tab', 'payroll');

        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');

        // Get retroactive adjustments for display
        $retroAdjustments = RetroactiveAdjustment::with(['employee', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('dashboard.payroll', compact(
            'payrolls',
            'departments',
            'settings',
            'payroll_alerts',
            'employees',
            'transactions',
            'unread_alerts_count',
            'allowances',
            'deductions',
            'activeTab',
            'periods',
            'retroAdjustments'
        ));
    }

    /**
     * View payroll details
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            $payroll = Payroll::select(
                    'payrolls.*',
                    'employees.department',
                    'employees.position',
                    'employees.email',
                    'employees.phone',
                    'employees.bank_name',
                    'employees.account_number',
                    'employees.nssf_number',
                    'employees.nhif_number',
                    'employees.tin_number'
                )
                ->leftJoin('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
                ->whereRaw('LOWER(payrolls.payroll_id) = ?', [strtolower($id)])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'payroll' => [
                    'payroll_id' => $payroll->payroll_id,
                    'period' => $payroll->period,
                    'net_salary' => $payroll->net_salary,
                    'status' => $payroll->status,
                    'employee_name' => $payroll->employee_name,
                    'employee_id' => $payroll->employee_id,
                    'department' => $payroll->department,
                    'position' => $payroll->position,
                    'email' => $payroll->email,
                    'phone' => $payroll->phone,
                    'base_salary' => $payroll->base_salary,
                    'allowances' => $payroll->allowances,
                    'deductions' => $payroll->deductions,
                    'total_amount' => $payroll->total_amount,
                    'employer_contributions' => $payroll->employer_contributions,
                    'payment_method' => $payroll->payment_method,
                    'bank_name' => $payroll->bank_name,
                    'account_number' => $payroll->account_number,
                    'nssf_number' => $payroll->nssf_number,
                    'nhif_number' => $payroll->nhif_number,
                    'tin_number' => $payroll->tin_number,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch payroll details', [
                'payroll_id' => $id,
                'user_id' => $user->id,
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payroll record not found'
            ], 404);
        }
    }

    /**
     * View transaction details
     */
    public function showTransaction($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            $transaction = Transaction::select(
                    'transactions.*',
                    'employees.department',
                    'employees.position',
                    'employees.email',
                    'employees.phone',
                    'employees.bank_name',
                    'employees.account_number'
                )
                ->leftJoin('employees', 'transactions.employee_id', '=', 'employees.employee_id')
                ->where('transactions.transaction_id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'transaction' => [
                    'transaction_id' => $transaction->transaction_id,
                    'employee_name' => $transaction->employee_name,
                    'employee_id' => $transaction->employee_id,
                    'department' => $transaction->department,
                    'position' => $transaction->position,
                    'email' => $transaction->email,
                    'phone' => $transaction->phone,
                    'amount' => $transaction->amount,
                    'transaction_date' => $transaction->transaction_date,
                    'status' => $transaction->status,
                    'type' => $transaction->type,
                    'payment_method' => $transaction->payment_method,
                    'description' => $transaction->description,
                    'bank_name' => $transaction->bank_name,
                    'account_number' => $transaction->account_number,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction record not found'
            ], 404);
        }
    }

    /**
     * View alert details
     */
    public function showAlert($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            $alert = PayrollAlert::select(
                    'payroll_alerts.*',
                    'employees.department',
                    'employees.position',
                    'employees.email',
                    'employees.phone',
                    'employees.name as employee_name'
                )
                ->leftJoin('employees', 'payroll_alerts.employee_id', '=', 'employees.employee_id')
                ->where('payroll_alerts.alert_id', $id)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'alert' => [
                    'alert_id' => $alert->alert_id,
                    'employee_name' => $alert->employee_name,
                    'employee_id' => $alert->employee_id,
                    'department' => $alert->department,
                    'position' => $alert->position,
                    'email' => $alert->email,
                    'phone' => $alert->phone,
                    'type' => $alert->type,
                    'message' => $alert->message,
                    'status' => $alert->status,
                    'created_at' => $alert->created_at,
                    'metadata' => $alert->metadata,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alert record not found'
            ], 404);
        }
    }

    /**
     * Mark alert as read
     */
    public function markAlertRead($id)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        try {
            $alert = PayrollAlert::where('alert_id', $id)->firstOrFail();
            
            // Get employee data before marking as read
            $employee = Employee::where('employee_id', $alert->employee_id)->first();
            $employeeDataBefore = $employee ? [
                'base_salary' => $employee->base_salary,
                'allowances' => $employee->allowances,
                'deductions' => $employee->deductions
            ] : null;

            $alert->update(['status' => 'Read']);

            // Get employee data after marking as read
            $employee->refresh();
            $employeeDataAfter = $employee ? [
                'base_salary' => $employee->base_salary,
                'allowances' => $employee->allowances,
                'deductions' => $employee->deductions
            ] : null;

            return response()->json([
                'success' => true,
                'message' => 'Alert marked as read.',
                'employee_changes' => [
                    'before' => $employeeDataBefore,
                    'after' => $employeeDataAfter
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark alert as read.'
            ], 500);
        }
    }

    /**
     * Run payroll processing - FIXED VALIDATION RULES
     */
public function run(Request $request)
{
    $user = Auth::user();
    if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
        return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can run payroll.');
    }

    Log::info('Payroll run request data:', $request->all());

    $validator = Validator::make($request->all(), [
        'payroll_period' => 'required|date_format:Y-m-d',
        'employee_selection' => 'required|in:all,single,multiple',
        'employee_id' => 'sometimes|required_if:employee_selection,single',
        'employee_ids' => 'sometimes|required_if:employee_selection,multiple|array',
        'employee_ids.*' => 'sometimes',
        'nssf_rate' => 'required|numeric|min:0|max:100',
        'nhif_rate' => 'required|numeric|min:0|max:100',
    ]);

    if ($validator->fails()) {
        Log::error('Payroll validation failed:', $validator->errors()->toArray());
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $period = Carbon::parse($request->payroll_period)->format('Y-m');
    $periodDisplay = Carbon::parse($request->payroll_period)->format('F Y');

    // Determine employees based on selection
    $employees = collect();
    $selectionType = "";

    try {
        if ($request->employee_selection === 'all') {
            $employees = Employee::where('status', 'active')->get();
            $selectionType = "all active employees";
            Log::info("Selected all active employees. Count: " . $employees->count());
        } elseif ($request->employee_selection === 'single') {
            if (empty($request->employee_id)) {
                return redirect()->back()
                    ->with('error', 'Please select an employee for single selection.')
                    ->withInput();
            }
            $employee = Employee::where('employee_id', $request->employee_id)->where('status', 'active')->first();
            if (!$employee) {
                return redirect()->back()
                    ->with('error', 'Selected employee not found or inactive.')
                    ->withInput();
            }
            $employees = collect([$employee]);
            $selectionType = "single employee";
            Log::info("Selected single employee: " . $request->employee_id);
        } else {
            $employeeIds = $request->employee_ids ?? [];
            if (empty($employeeIds)) {
                return redirect()->back()
                    ->with('error', 'Please select at least one employee for multiple selection.')
                    ->withInput();
            }
            $employees = Employee::whereIn('employee_id', $employeeIds)->where('status', 'active')->get();
            if ($employees->count() !== count($employeeIds)) {
                return redirect()->back()
                    ->with('error', 'One or more selected employees not found or inactive.')
                    ->withInput();
            }
            $selectionType = "multiple employees (" . count($employees) . " employees)";
            Log::info("Selected multiple employees. Count: " . $employees->count());
        }

        if ($employees->isEmpty()) {
            Log::warning('No active employees found for payroll processing');
            return redirect()->back()
                ->with('warning', 'No active employees found for payroll processing.')
                ->withInput();
        }

        DB::beginTransaction();

        $processedCount = 0;
        $failedEmployees = [];

        foreach ($employees as $employee) {
            try {
                Log::info("Processing payroll for employee: " . $employee->employee_id . " - " . $employee->name);
                Log::info("Current employee data - Base Salary: " . $employee->base_salary . ", Allowances: " . $employee->allowances . ", Deductions: " . $employee->deductions);

                // USE CURRENT EMPLOYEE DATA (INCLUDING RETROACTIVE ADJUSTMENTS)
                $baseSalary = $employee->base_salary ?? 0;
                $currentAllowances = $employee->allowances ?? 0;
                $currentDeductions = $employee->deductions ?? 0;

                Log::info("Using current employee data - Base Salary: " . $baseSalary . ", Allowances: " . $currentAllowances);

                if ($baseSalary <= 0) {
                    throw new \Exception("Invalid base salary: " . $baseSalary);
                }

                // Calculate additional allowances from allowance table
                $additionalAllowances = $this->calculateAllowances($employee);
                $totalAdditionalAllowances = array_sum(array_column($additionalAllowances, 'amount'));
                
                // Total allowances = current allowances + additional allowances
                $totalAllowances = $currentAllowances + $totalAdditionalAllowances;
                $grossSalary = $baseSalary + $totalAllowances;

                Log::info("Gross salary calculated - Base: " . $baseSalary . ", Allowances: " . $totalAllowances . ", Gross: " . $grossSalary);

                if ($grossSalary <= 0) {
                    throw new \Exception("Invalid gross salary: " . $grossSalary);
                }

                // Use rates from form input
                $nssfRate = $request->nssf_rate;
                $nhifRate = $request->nhif_rate;

                Log::info("Using rates - NSSF: " . $nssfRate . "%, NHIF: " . $nhifRate . "%");

                $deductions = $this->calculateDeductions($employee, $grossSalary, $nssfRate, $nhifRate);
                $totalCalculatedDeductions = array_sum(array_column($deductions, 'amount'));
                
                // Total deductions = current deductions + calculated deductions
                $totalDeductions = $currentDeductions + $totalCalculatedDeductions;
                $netSalary = $grossSalary - $totalDeductions;

                Log::info("Deductions - Current: " . $currentDeductions . ", Calculated: " . $totalCalculatedDeductions . ", Total: " . $totalDeductions . ", Net salary: " . $netSalary);

                $employerContributions = $this->calculateEmployerContributions($grossSalary, $nssfRate, $nhifRate);
                $totalEmployerContributions = array_sum(array_column($employerContributions, 'amount'));

                $payrollId = $this->generateRandomId('PAY');
                $payslipId = $this->generateRandomId('PSLIP');
                $transactionId = $this->generateRandomId('TRX');

                // Count payrolls for this employee in this period
                $payrollCount = Payroll::where('employee_id', $employee->employee_id)
                    ->where('period', $period)
                    ->count() + 1;

                Log::info("Creating payroll record #" . $payrollCount . " for employee: " . $employee->employee_id);

                // CREATE PAYROLL RECORD
                $payrollData = [
                    'payroll_id' => $payrollId,
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->name,
                    'period' => $period,
                    'base_salary' => $baseSalary,
                    'allowances' => $totalAllowances,
                    'total_amount' => $grossSalary,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'employer_contributions' => $totalEmployerContributions,
                    'status' => 'Processed',
                    'payment_method' => $employee->bank_name ? 'Bank Transfer' : 'Cash',
                    'created_by' => $user->id,
                ];

                $payroll = Payroll::create($payrollData);
                Log::info("Payroll record created: " . $payrollId);

                // CREATE PAYSLIP RECORD
                $payslipData = [
                    'payslip_id' => $payslipId,
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->name,
                    'period' => $period,
                    'base_salary' => $baseSalary,
                    'allowances' => $totalAllowances,
                    'deductions' => $totalDeductions,
                    'net_salary' => $netSalary,
                    'status' => 'Generated',
                ];

                Payslip::create($payslipData);
                Log::info("Payslip record created: " . $payslipId);

                // CREATE TRANSACTION RECORD
                $transactionDescription = "Salary payment for {$periodDisplay}";
                
                // Add note if there are retroactive adjustments
                if ($currentAllowances > 0 || $currentDeductions > 0) {
                    $adjustmentNotes = [];
                    if ($currentAllowances > 0) {
                        $adjustmentNotes[] = "includes retroactive allowances: TZS " . number_format($currentAllowances, 0);
                    }
                    if ($currentDeductions > 0) {
                        $adjustmentNotes[] = "includes retroactive deductions: TZS " . number_format($currentDeductions, 0);
                    }
                    $transactionDescription .= " (" . implode(", ", $adjustmentNotes) . ")";
                }

                $transactionData = [
                    'transaction_id' => $transactionId,
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->name,
                    'type' => 'salary_payment',
                    'amount' => $netSalary,
                    'transaction_date' => now(),
                    'status' => 'Completed',
                    'payment_method' => $employee->bank_name ? 'Bank Transfer' : 'Cash',
                    'description' => $transactionDescription
                ];

                Transaction::create($transactionData);
                Log::info("Transaction record created: " . $transactionId);

                // ALERT FOR HIGH DEDUCTIONS
                if ($totalDeductions > 0 && ($totalDeductions / $grossSalary) > 0.5) {
                    $alertId = $this->generateRandomId('ALRT');
                    PayrollAlert::create([
                        'alert_id' => $alertId,
                        'employee_id' => $employee->employee_id,
                        'type' => 'High Deductions',
                        'message' => "Deductions for {$employee->name} exceed 50% of gross salary for {$periodDisplay}.",
                        'status' => 'Unread'
                    ]);
                    Log::info("High deduction alert created for employee: " . $employee->employee_id);
                }

                $processedCount++;
                Log::info("Successfully processed payroll for employee: " . $employee->employee_id);

            } catch (\Exception $e) {
                $errorMsg = $employee->name . " (" . $e->getMessage() . ")";
                $failedEmployees[] = $errorMsg;
                Log::error('Failed to process payroll for employee: ' . $employee->employee_id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        if ($processedCount === 0) {
            DB::rollBack();
            $errorMessage = 'No payroll records were processed due to errors. ';
            if (!empty($failedEmployees)) {
                $errorMessage .= 'Errors: ' . implode(', ', $failedEmployees);
            }
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }

        DB::commit();

        $message = "Payroll processed successfully for {$processedCount} employee(s) for {$periodDisplay} ({$selectionType}).";
        $message .= " All retroactive adjustments have been included in the calculations.";

        if (!empty($failedEmployees)) {
            $message .= " Failed for " . count($failedEmployees) . " employee(s): " . implode(', ', $failedEmployees);
            // NOTIFICATION: Payroll imekamilika kwa onyo
            return redirect()->route('payroll')
                ->with('warning', $message);
        }

        // NOTIFICATION: Payroll imekamilika
        return redirect()->route('payroll')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payroll processing failed: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'period' => $period,
            'trace' => $e->getTraceAsString()
        ]);
        // NOTIFICATION: Payroll imeshindwa
        return redirect()->back()
            ->with('error', 'Failed to process payroll: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Process retroactive pay - COMPLETE FIXED VERSION
     */
public function retro(Request $request)
{
    $user = Auth::user();
    if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
        return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can process retroactive pay.');
    }

    $validator = Validator::make($request->all(), [
        'retro_period' => 'required|date_format:Y-m-d',
        'employee_selection' => 'required|in:all,single,multiple',
        'employee_id' => 'sometimes|required_if:employee_selection,single',
        'employee_ids' => 'sometimes|required_if:employee_selection,multiple|array',
        'employee_ids.*' => 'sometimes',
        'adjustment_type' => 'required|in:allowance,deduction,salary_adjustment',
        'adjustment_amount' => 'required|numeric',
        'adjustment_reason' => 'required|string|max:255',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    $period = Carbon::parse($request->retro_period)->format('Y-m');
    $periodDisplay = Carbon::parse($request->retro_period)->format('F Y');
    $adjustmentType = $request->adjustment_type;
    $adjustmentAmount = $request->adjustment_amount;
    $adjustmentReason = $request->adjustment_reason;

    $employees = collect();
    $selectionType = "";

    try {
        // Employee selection logic
        if ($request->employee_selection === 'all') {
            $employees = Employee::where('status', 'active')->get();
            $selectionType = "all active employees";
        } elseif ($request->employee_selection === 'single') {
            if (empty($request->employee_id)) {
                return redirect()->back()->with('error', 'Please select an employee.')->withInput();
            }
            $employee = Employee::where('employee_id', $request->employee_id)->where('status', 'active')->first();
            if (!$employee) {
                return redirect()->back()->with('error', 'Selected employee not found or inactive.')->withInput();
            }
            $employees = collect([$employee]);
            $selectionType = "single employee";
        } else {
            $employeeIds = $request->employee_ids ?? [];
            if (empty($employeeIds)) {
                return redirect()->back()->with('error', 'Please select employees.')->withInput();
            }
            $employees = Employee::whereIn('employee_id', $employeeIds)->where('status', 'active')->get();
            if ($employees->count() !== count($employeeIds)) {
                return redirect()->back()->with('error', 'One or more selected employees not found or inactive.')->withInput();
            }
            $selectionType = "multiple employees (" . count($employees) . " employees)";
        }

        if ($employees->isEmpty()) {
            return redirect()->back()->with('error', 'No active employees found.')->withInput();
        }

        DB::beginTransaction();

        $processedCount = 0;
        $failedEmployees = [];
        $recalculatedPayrolls = [];
        $recalculationDetails = [];

        foreach ($employees as $employee) {
            try {
                // Store original values for comparison
                $originalBaseSalary = $employee->base_salary;
                $originalAllowances = $employee->allowances;
                $originalDeductions = $employee->deductions;

                Log::info("Starting retroactive adjustment for employee: " . $employee->employee_id, [
                    'adjustment_type' => $adjustmentType,
                    'adjustment_amount' => $adjustmentAmount,
                    'original_values' => [
                        'base_salary' => $originalBaseSalary,
                        'allowances' => $originalAllowances,
                        'deductions' => $originalDeductions
                    ]
                ]);

                // CREATE RETROACTIVE ADJUSTMENT RECORD
                $adjustmentId = $this->generateRandomId('RADJ');

                $retroAdjustment = RetroactiveAdjustment::create([
                    'adjustment_id' => $adjustmentId,
                    'employee_id' => $employee->id,
                    'period' => $period,
                    'type' => $adjustmentType,
                    'amount' => $adjustmentAmount,
                    'reason' => $adjustmentReason,
                    'status' => 'pending',
                    'created_by' => $user->id,
                ]);

                // IMMEDIATELY APPLY THE ADJUSTMENT TO EMPLOYEE RECORD
                $this->applyRetroactiveAdjustment($employee, $adjustmentType, $adjustmentAmount);

                // RELOAD EMPLOYEE TO GET UPDATED VALUES
                $employee->refresh();

                Log::info("After retroactive adjustment - Employee refreshed", [
                    'employee_id' => $employee->employee_id,
                    'new_base_salary' => $employee->base_salary,
                    'new_allowances' => $employee->allowances,
                    'new_deductions' => $employee->deductions
                ]);

                // RECALCULATE EXISTING PAYROLLS FOR THIS EMPLOYEE IN THE AFFECTED PERIOD
                $affectedPayrolls = Payroll::where('employee_id', $employee->employee_id)
                    ->where('period', $period)
                    ->get();

                Log::info("Found {$affectedPayrolls->count()} payrolls to recalculate for period: {$period}");

                $employeeRecalculationDetails = [];

                foreach ($affectedPayrolls as $payroll) {
                    $recalculatedData = $this->recalculatePayroll($payroll, $employee);
                    
                    if ($recalculatedData) {
                        // Store old values before update
                        $oldPayrollData = [
                            'base_salary' => $payroll->base_salary,
                            'allowances' => $payroll->allowances,
                            'deductions' => $payroll->deductions,
                            'net_salary' => $payroll->net_salary,
                        ];

                        $payroll->update($recalculatedData);
                        $recalculatedPayrolls[] = $payroll->payroll_id;
                        
                        // Store recalculation details
                        $employeeRecalculationDetails[] = [
                            'payroll_id' => $payroll->payroll_id,
                            'old_values' => $oldPayrollData,
                            'new_values' => $recalculatedData
                        ];

                        Log::info("Payroll successfully recalculated", [
                            'payroll_id' => $payroll->payroll_id,
                            'old_net_salary' => $oldPayrollData['net_salary'],
                            'new_net_salary' => $recalculatedData['net_salary'],
                            'changes' => [
                                'base_salary' => $recalculatedData['base_salary'] - $oldPayrollData['base_salary'],
                                'allowances' => $recalculatedData['allowances'] - $oldPayrollData['allowances'],
                                'deductions' => $recalculatedData['deductions'] - $oldPayrollData['deductions'],
                                'net_salary' => $recalculatedData['net_salary'] - $oldPayrollData['net_salary'],
                            ]
                        ]);
                    } else {
                        Log::error("Failed to recalculate payroll: " . $payroll->payroll_id);
                    }
                }

                $recalculationDetails[$employee->employee_id] = $employeeRecalculationDetails;

                // CREATE TRANSACTION RECORD
                $transactionId = $this->generateRandomId('TRX');
                $transactionType = $adjustmentType === 'deduction' ? 'retroactive_deduction' : 'retroactive_adjustment';

                Transaction::create([
                    'transaction_id' => $transactionId,
                    'employee_id' => $employee->employee_id,
                    'employee_name' => $employee->name,
                    'type' => $transactionType,
                    'amount' => $adjustmentAmount,
                    'transaction_date' => now(),
                    'status' => 'Completed',
                    'payment_method' => 'Adjustment',
                    'description' => "Retroactive {$adjustmentType} for {$periodDisplay}: {$adjustmentReason}. Payroll recalculated."
                ]);

                // CREATE ALERT WITH DETAILED CHANGES
                $alertId = $this->generateRandomId('ALRT');
                
                $changeDetails = $this->getChangeDetails([
                    'base_salary' => ['old' => $originalBaseSalary, 'new' => $employee->base_salary],
                    'allowances' => ['old' => $originalAllowances, 'new' => $employee->allowances],
                    'deductions' => ['old' => $originalDeductions, 'new' => $employee->deductions],
                ]);

                // Add payroll recalculation info to message
                $recalcInfo = "";
                if (!empty($employeeRecalculationDetails)) {
                    $netSalaryChanges = array_map(function($detail) {
                        return $detail['new_values']['net_salary'] - $detail['old_values']['net_salary'];
                    }, $employeeRecalculationDetails);
                    
                    $totalNetChange = array_sum($netSalaryChanges);
                    $recalcInfo = " Payrolls recalculated: " . count($employeeRecalculationDetails) . 
                                 " | Net salary change: TZS " . number_format($totalNetChange, 0);
                }

                PayrollAlert::create([
                    'alert_id' => $alertId,
                    'employee_id' => $employee->employee_id,
                    'type' => 'Retroactive Adjustment Applied',
                    'message' => "Retroactive {$adjustmentType} of TZS " . number_format($adjustmentAmount, 0) . 
                                " has been applied to {$employee->name}'s record for period {$periodDisplay}. " .
                                "Reason: {$adjustmentReason}. Changes: {$changeDetails}.{$recalcInfo}",
                    'status' => 'Unread',
                    'metadata' => json_encode([
                        'adjustment_id' => $adjustmentId,
                        'adjustment_type' => $adjustmentType,
                        'amount' => $adjustmentAmount,
                        'period' => $periodDisplay,
                        'changes' => [
                            'base_salary' => ['old' => $originalBaseSalary, 'new' => $employee->base_salary],
                            'allowances' => ['old' => $originalAllowances, 'new' => $employee->allowances],
                            'deductions' => ['old' => $originalDeductions, 'new' => $employee->deductions],
                        ],
                        'recalculated_payrolls' => $recalculatedPayrolls,
                        'recalculation_details' => $employeeRecalculationDetails,
                        'employee_name' => $employee->name
                    ])
                ]);

                // UPDATE RETROACTIVE ADJUSTMENT STATUS TO APPLIED
                $retroAdjustment->update([
                    'status' => 'applied',
                    'applied_at' => now()
                ]);

                $processedCount++;
                Log::info("Retroactive adjustment completed successfully for employee: " . $employee->employee_id, [
                    'adjustment_id' => $adjustmentId,
                    'type' => $adjustmentType,
                    'amount' => $adjustmentAmount,
                    'changes' => [
                        'base_salary' => ['old' => $originalBaseSalary, 'new' => $employee->base_salary],
                        'allowances' => ['old' => $originalAllowances, 'new' => $employee->allowances],
                        'deductions' => ['old' => $originalDeductions, 'new' => $employee->deductions],
                    ],
                    'recalculated_payrolls_count' => count($employeeRecalculationDetails)
                ]);

            } catch (\Exception $e) {
                $failedEmployees[] = $employee->name . " (" . $e->getMessage() . ")";
                Log::error('Failed to process retroactive adjustment for employee: ' . $employee->employee_id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        DB::commit();

        $message = "Retroactive {$adjustmentType} applied successfully for {$processedCount} employee(s) for {$periodDisplay} ({$selectionType}). ";
        $message .= "Employee records have been updated and payrolls have been recalculated.";

        if (!empty($recalculatedPayrolls)) {
            $message .= " Recalculated " . count($recalculatedPayrolls) . " payroll record(s).";
        }

        if (!empty($failedEmployees)) {
            $message .= " Failed for " . count($failedEmployees) . " employee(s): " . implode(', ', $failedEmployees);
            // NOTIFICATION: Marekebisho yamekamilika kwa onyo
            return redirect()->route('payroll')
                ->with('warning', $message);
        }

        // NOTIFICATION: Marekebisho ya nyuma yamekamilika
        return redirect()->route('payroll')
            ->with('success', $message);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Retroactive pay processing failed: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'period' => $period,
            'adjustment_type' => $request->adjustment_type,
            'amount' => $request->adjustment_amount,
            'trace' => $e->getTraceAsString()
        ]);
        // NOTIFICATION: Marekebisho yameshindwa
        return redirect()->back()
            ->with('error', 'Failed to process retroactive pay: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Recalculate payroll after retroactive adjustment - COMPLETE VERSION
     */
    private function recalculatePayroll($payroll, $employee)
    {
        try {
            Log::info("Starting payroll recalculation for payroll: " . $payroll->payroll_id, [
                'employee_id' => $employee->employee_id,
                'current_base_salary' => $employee->base_salary,
                'current_allowances' => $employee->allowances,
                'current_deductions' => $employee->deductions
            ]);

            // Use current employee data (with retroactive adjustments)
            $baseSalary = $employee->base_salary ?? 0;
            $currentAllowances = $employee->allowances ?? 0;
            $currentDeductions = $employee->deductions ?? 0;

            if ($baseSalary <= 0) {
                throw new \Exception("Invalid base salary: " . $baseSalary);
            }

            // Calculate additional allowances from allowance table
            $additionalAllowances = $this->calculateAllowances($employee);
            $totalAdditionalAllowances = array_sum(array_column($additionalAllowances, 'amount'));
            
            // Total allowances = current allowances + additional allowances
            $totalAllowances = $currentAllowances + $totalAdditionalAllowances;
            $grossSalary = $baseSalary + $totalAllowances;

            Log::info("Recalculation - Base: {$baseSalary}, Allowances: {$totalAllowances}, Gross: {$grossSalary}");

            if ($grossSalary <= 0) {
                throw new \Exception("Invalid gross salary: " . $grossSalary);
            }

            // Use rates from settings
            $settings = $this->getSettings();
            $nssfRate = $settings['nssf_employee_rate'] ?? 10.0;
            $nhifRate = $settings['nhif_employee_rate'] ?? 3.0;

            Log::info("Using rates for recalculation - NSSF: {$nssfRate}%, NHIF: {$nhifRate}%");

            // Recalculate ALL deductions based on new gross salary
            $deductions = $this->calculateDeductions($employee, $grossSalary, $nssfRate, $nhifRate);
            $totalCalculatedDeductions = array_sum(array_column($deductions, 'amount'));
            
            // Total deductions = current deductions + calculated deductions
            $totalDeductions = $currentDeductions + $totalCalculatedDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            Log::info("Recalculation - Deductions: {$totalDeductions}, Net Salary: {$netSalary}");

            // Recalculate employer contributions
            $employerContributions = $this->calculateEmployerContributions($grossSalary, $nssfRate, $nhifRate);
            $totalEmployerContributions = array_sum(array_column($employerContributions, 'amount'));

            $recalculatedData = [
                'base_salary' => $baseSalary,
                'allowances' => $totalAllowances,
                'total_amount' => $grossSalary,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'employer_contributions' => $totalEmployerContributions,
            ];

            Log::info("Payroll recalculation completed successfully", [
                'payroll_id' => $payroll->payroll_id,
                'recalculated_data' => $recalculatedData
            ]);

            return $recalculatedData;

        } catch (\Exception $e) {
            Log::error('Failed to recalculate payroll: ' . $e->getMessage(), [
                'payroll_id' => $payroll->payroll_id,
                'employee_id' => $employee->employee_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Get formatted change details for alert message
     */
    private function getChangeDetails($changes)
    {
        $details = [];
        
        foreach ($changes as $field => $values) {
            if ($values['old'] != $values['new']) {
                $fieldName = str_replace('_', ' ', ucfirst($field));
                $details[] = "{$fieldName}: TZS " . number_format($values['old'], 0) . 
                            " → TZS " . number_format($values['new'], 0);
            }
        }
        
        return implode('; ', $details);
    }

    /**
     * Apply retroactive adjustment to employee record
     */
    private function applyRetroactiveAdjustment($employee, $adjustmentType, $adjustmentAmount)
    {
        $currentAllowances = $employee->allowances ?? 0;
        $currentBaseSalary = $employee->base_salary ?? 0;
        $currentDeductions = $employee->deductions ?? 0;

        switch ($adjustmentType) {
            case 'salary_adjustment':
                // Add to base salary
                $employee->update([
                    'base_salary' => $currentBaseSalary + $adjustmentAmount
                ]);
                break;

            case 'allowance':
                // Add to allowances
                $employee->update([
                    'allowances' => $currentAllowances + $adjustmentAmount
                ]);
                break;

            case 'deduction':
                // Add to deductions
                $employee->update([
                    'deductions' => $currentDeductions + $adjustmentAmount
                ]);
                break;
        }

        // Reload the employee to get updated values
        $employee->refresh();

        Log::info("Applied retroactive adjustment to employee", [
            'employee_id' => $employee->employee_id,
            'adjustment_type' => $adjustmentType,
            'amount' => $adjustmentAmount,
            'old_base_salary' => $currentBaseSalary,
            'new_base_salary' => $employee->base_salary,
            'old_allowances' => $currentAllowances,
            'new_allowances' => $employee->allowances,
            'old_deductions' => $currentDeductions,
            'new_deductions' => $employee->deductions
        ]);
    }

    /**
     * Revert a specific payroll or payrolls for a period - Admin and HR only
     */
public function revert(Request $request)
{
    $user = Auth::user();
    if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
        return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can revert payroll.');
    }

    $validator = Validator::make($request->all(), [
        'period' => 'nullable|date_format:Y-m',
        'payroll_id' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return redirect()->back()
            ->withErrors($validator)
            ->withInput();
    }

    try {
        DB::beginTransaction();

        if ($request->has('period') && !empty($request->period)) {
            $period = $request->period;
            $payrolls = Payroll::where('period', $period)->get();

            if ($payrolls->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No payroll records found for the specified period.')
                    ->withInput();
            }

            $revertedCount = 0;

            foreach ($payrolls as $payroll) {
                $this->deleteRelatedRecords($payroll);
                $payroll->delete();

                $alertId = $this->generateRandomId('ALRT');
                PayrollAlert::create([
                    'alert_id' => $alertId,
                    'employee_id' => $payroll->employee_id,
                    'type' => 'Payroll Reverted',
                    'message' => "Payroll {$payroll->payroll_id} for {$payroll->employee_name} in period {$period} has been reverted.",
                    'status' => 'Read'
                ]);

                $revertedCount++;
            }

            DB::commit();
            // NOTIFICATION: Payroll imezimwa
            return redirect()->route('payroll')
                ->with('success', "Successfully reverted {$revertedCount} payroll record(s) for period {$period}.");
        }

        if ($request->has('payroll_id') && !empty($request->payroll_id)) {
            $payrollId = $request->payroll_id;

            $payroll = Payroll::whereRaw('LOWER(payroll_id) = ?', [strtolower($payrollId)])->first();
            if (!$payroll) {
                return redirect()->back()
                    ->with('error', 'Payroll record not found for the specified ID.')
                    ->withInput();
            }

            $this->deleteRelatedRecords($payroll);
            $payroll->delete();

            $alertId = $this->generateRandomId('ALRT');
            PayrollAlert::create([
                'alert_id' => $alertId,
                'employee_id' => $payroll->employee_id,
                'type' => 'Payroll Reverted',
                'message' => "Payroll {$payrollId} for {$payroll->employee_name} has been reverted.",
                'status' => 'Read'
            ]);

            DB::commit();
            // NOTIFICATION: Payroll imezimwa
            return redirect()->route('payroll')
                ->with('success', "Payroll record {$payrollId} has been reverted successfully.");
        }

        return redirect()->back()
            ->with('error', 'Either period or payroll ID is required.')
            ->withInput();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Payroll revert failed: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'payroll_id' => $request->payroll_id ?? 'N/A',
            'period' => $request->period ?? 'N/A',
            'trace' => $e->getTraceAsString()
        ]);
        // NOTIFICATION: Kuzima kumeshindwa
        return redirect()->back()
            ->with('error', 'Failed to revert payroll: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Revert retroactive adjustments
     */
    public function revertRetroactive(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can revert retroactive adjustments.');
        }

        // FIXED: Validation rule for employee_id
        $validator = Validator::make($request->all(), [
            'period' => 'required|date_format:Y-m',
            'employee_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $period = $request->period;
            $employeeId = $request->employee_id;

            $query = RetroactiveAdjustment::where('period', $period);

            if ($employeeId) {
                // Find employee by employee_id to get internal ID
                $employee = Employee::where('employee_id', $employeeId)->first();
                if ($employee) {
                    $query->where('employee_id', $employee->id);
                }
            }

            $adjustments = $query->get();
            $revertedCount = 0;

            foreach ($adjustments as $adjustment) {
                $adjustment->update(['status' => 'reverted']);
                $revertedCount++;
            }

            DB::commit();

            $message = "Successfully reverted {$revertedCount} retroactive adjustment(s) for period " . Carbon::parse($period)->format('F Y');

            if ($employeeId) {
                $employee = Employee::where('employee_id', $employeeId)->first();
                $message .= " for employee: " . ($employee->name ?? '');
            }

            return redirect()->route('payroll')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revert retroactive adjustments failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'period' => $request->period,
                'employee_id' => $request->employee_id,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to revert retroactive adjustments: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Revert all specified data - Admin and HR only - FIXED VALIDATION RULES
     */
    public function revertAll(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized. Only Admin and HR can revert all data.');
        }

        // FIXED: Validation rules for employee_id
        $validator = Validator::make($request->all(), [
            'revert_types' => 'required|array',
            'revert_types.*' => 'in:payroll,transactions,alerts,retroactive',
            'period' => 'nullable|date_format:Y-m',
            'employee_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $revertTypes = $request->input('revert_types', []);
            $period = $request->input('period');
            $employeeId = $request->input('employee_id');

            $queryConditions = [];
            if ($period) {
                $queryConditions[] = ['period', '=', $period];
            }
            if ($employeeId) {
                $queryConditions[] = ['employee_id', '=', $employeeId];
            }

            $revertedCount = 0;

            if (in_array('payroll', $revertTypes)) {
                $payrollQuery = Payroll::where($queryConditions);
                $payrollCount = $payrollQuery->count();

                $payrolls = $payrollQuery->get();
                foreach ($payrolls as $payroll) {
                    $this->deleteRelatedRecords($payroll);
                }

                $payrollQuery->delete();
                $revertedCount += $payrollCount;
            }

            if (in_array('transactions', $revertTypes)) {
                $transactionConditions = [];
                if ($employeeId) {
                    $transactionConditions[] = ['employee_id', '=', $employeeId];
                }
                if ($period) {
                    $startDate = Carbon::parse($period)->startOfMonth();
                    $endDate = Carbon::parse($period)->endOfMonth();
                    $transactionQuery = Transaction::where($transactionConditions)
                        ->whereBetween('transaction_date', [$startDate, $endDate]);
                } else {
                    $transactionQuery = Transaction::where($transactionConditions);
                }

                $transactionCount = $transactionQuery->count();
                $transactionQuery->delete();
                $revertedCount += $transactionCount;
            }

            if (in_array('alerts', $revertTypes)) {
                $alertConditions = [];
                if ($employeeId) {
                    $alertConditions[] = ['employee_id', '=', $employeeId];
                }
                if ($period) {
                    $startDate = Carbon::parse($period)->startOfMonth();
                    $endDate = Carbon::parse($period)->endOfMonth();
                    $alertQuery = PayrollAlert::where($alertConditions)
                        ->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $alertQuery = PayrollAlert::where($alertConditions);
                }

                $alertCount = $alertQuery->count();
                $alertQuery->delete();
                $revertedCount += $alertCount;
            }

            if (in_array('retroactive', $revertTypes)) {
                $retroConditions = [];
                if ($employeeId) {
                    // Find employee by employee_id to get internal ID
                    $employee = Employee::where('employee_id', $employeeId)->first();
                    if ($employee) {
                        $retroConditions[] = ['employee_id', '=', $employee->id];
                    }
                }
                if ($period) {
                    $retroQuery = RetroactiveAdjustment::where($retroConditions)
                        ->where('period', $period);
                } else {
                    $retroQuery = RetroactiveAdjustment::where($retroConditions);
                }

                $retroCount = $retroQuery->count();
                $retroQuery->delete();
                $revertedCount += $retroCount;
            }

            DB::commit();

            $message = "Successfully reverted {$revertedCount} records.";
            if ($period) {
                $message .= " Period: " . Carbon::parse($period)->format('F Y');
            }
            if ($employeeId) {
                $employee = Employee::where('employee_id', $employeeId)->first();
                $message .= $employee ? " Employee: " . $employee->name : "";
            }

            return redirect()->route('payroll')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Revert all data failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'revert_types' => $revertTypes,
                'period' => $period,
                'employee_id' => $employeeId,
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()
                ->with('error', 'Failed to revert data: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Helper method to delete related records for a payroll
     */
    private function deleteRelatedRecords($payroll)
    {
        Payslip::where('employee_id', $payroll->employee_id)
            ->where('period', $payroll->period)
            ->delete();

        $descriptionPeriod = Carbon::parse($payroll->period)->format('F Y');
        Transaction::where('employee_id', $payroll->employee_id)
            ->where('transaction_date', '>=', Carbon::parse($payroll->period)->startOfMonth())
            ->where('transaction_date', '<', Carbon::parse($payroll->period)->endOfMonth()->addDay())
            ->where('description', 'like', '%' . $descriptionPeriod . '%')
            ->delete();

        // Always delete alerts related to this payroll
        PayrollAlert::where('employee_id', $payroll->employee_id)
            ->where('created_at', '>=', Carbon::parse($payroll->period)->startOfMonth())
            ->where('created_at', '<', Carbon::parse($payroll->period)->endOfMonth()->addDay())
            ->where('message', 'like', '%' . $payroll->payroll_id . '%')
            ->delete();
    }

    /**
     * Calculate allowances for employee
     */
    private function calculateAllowances($employee)
    {
        $allowances = Allowance::join('employee_allowance', 'allowance.id', '=', 'employee_allowance.allowance_id')
            ->where('employee_allowance.employee_id', $employee->employee_id)
            ->where('allowance.active', 1)
            ->whereNull('allowance.deleted_at')
            ->get(['allowance.*']);

        $result = [];

        foreach ($allowances as $allowance) {
            $amount = $allowance->type === 'percentage'
                ? round($employee->base_salary * ($allowance->amount / 100), 0)
                : round($allowance->amount, 0);

            $result[] = [
                'name' => $allowance->name,
                'amount' => $amount,
                'taxable' => $allowance->taxable
            ];
        }

        return $result;
    }

    /**
     * Calculate deductions with dynamic rates
     */
    private function calculateDeductions($employee, $grossSalary, $nssfRate, $nhifRate)
    {
        if ($grossSalary <= 0) {
            throw new \Exception("Invalid gross salary for employee ID {$employee->employee_id}");
        }

        $result = [];

        // NSSF: Dynamic rate from form (employee share)
        $nssf = round($grossSalary * ($nssfRate / 100), 0);
        $result[] = ['name' => 'NSSF', 'amount' => $nssf, 'category' => 'statutory'];

        // NHIF: Dynamic rate from form (employee share)
        $nhif = round($grossSalary * ($nhifRate / 100), 0);
        $result[] = ['name' => 'NHIF', 'amount' => $nhif, 'category' => 'statutory'];

        // Calculate taxable income
        $allowances = $this->calculateAllowances($employee);
        $taxableAllowances = array_sum(
            array_map(
                fn($allowance) => $allowance['taxable'] ? $allowance['amount'] : 0,
                $allowances
            )
        );
        $taxableIncome = $employee->base_salary + $taxableAllowances - $nssf;

        // PAYE
        $paye = round($this->calculatePAYE($taxableIncome), 0);
        $result[] = ['name' => 'PAYE', 'amount' => $paye, 'category' => 'statutory'];

        // Other deductions per employee
        $deductions = Deduction::join('employee_deduction', 'deductions.id', '=', 'employee_deduction.deduction_id')
            ->where('employee_deduction.employee_id', $employee->employee_id)
            ->where('deductions.active', 1)
            ->whereNull('deductions.deleted_at')
            ->get(['deductions.*']);

        foreach ($deductions as $deduction) {
            $amount = $deduction->type === 'percentage'
                ? round($employee->base_salary * ($deduction->amount / 100), 0)
                : round($deduction->amount, 0);

            $result[] = [
                'name' => $deduction->name,
                'amount' => $amount,
                'category' => $deduction->category
            ];
        }

        return $result;
    }

    /**
     * Calculate employer contributions (WCF, SDL, employer NSSF, employer NHIF)
     */
    private function calculateEmployerContributions($grossSalary, $nssfRate, $nhifRate)
    {
        $result = [];

        // Employer NSSF (matching employee share)
        $employerNssf = round($grossSalary * ($nssfRate / 100), 0);
        $result[] = ['name' => 'NSSF Employer', 'amount' => $employerNssf, 'category' => 'employer'];

        // Employer NHIF (matching employee share)
        $employerNhif = round($grossSalary * ($nhifRate / 100), 0);
        $result[] = ['name' => 'NHIF Employer', 'amount' => $employerNhif, 'category' => 'employer'];

        // WCF: 0.5% employer contribution
        $wcf = round($grossSalary * 0.005, 0);
        $result[] = ['name' => 'WCF', 'amount' => $wcf, 'category' => 'employer'];

        // SDL: 3.5% employer contribution
        $sdl = round($grossSalary * 0.035, 0);
        $result[] = ['name' => 'SDL', 'amount' => $sdl, 'category' => 'employer'];

        return $result;
    }

    /**
     * Calculate PAYE based on Tanzanian tax brackets
     */
    private function calculatePAYE($taxableIncome)
    {
        $settings = $this->getSettings();
        $taxFreeThreshold = $settings['paye_tax_free'] ?? 270000;

        if ($taxableIncome <= $taxFreeThreshold) {
            return 0;
        } elseif ($taxableIncome <= 520000) {
            return ($taxableIncome - $taxFreeThreshold) * 0.08;
        } elseif ($taxableIncome <= 760000) {
            return 20000 + ($taxableIncome - 520000) * 0.20;
        } elseif ($taxableIncome <= 1000000) {
            return 68000 + ($taxableIncome - 760000) * 0.25;
        } else {
            return 128000 + ($taxableIncome - 1000000) * 0.30;
        }
    }

    /**
     * Retrieve global settings
     */
    private function getSettings()
    {
        return Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Generate random ID with prefix
     */
    private function generateRandomId($prefix)
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $random = strtoupper(bin2hex(random_bytes(4)));
            $newId = $prefix . '-' . $random;

            $isUnique = !Payroll::where('payroll_id', $newId)->exists() &&
                        !Transaction::where('transaction_id', $newId)->exists() &&
                        !PayrollAlert::where('alert_id', $newId)->exists() &&
                        !Payslip::where('payslip_id', $newId)->exists() &&
                        !RetroactiveAdjustment::where('adjustment_id', $newId)->exists();

            if ($isUnique) {
                return $newId;
            }

            $attempt++;
        }

        $timestamp = now()->format('YmdHis');
        $fallbackId = $prefix . '-' . $timestamp . '-' . rand(1000, 9999);
        return $fallbackId;
    }
}