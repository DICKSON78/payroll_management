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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

        // Admin/HR can see all payrolls
        $payrolls = Payroll::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $payroll_alerts = PayrollAlert::with('employee')
            ->whereIn('status', ['Unread', 'Read'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $employees = Employee::all();
        $transactions = Transaction::with('employee')
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);

        $departments = Employee::distinct()->pluck('department');
        $settings = $this->getSettings();
        $allowances = Allowance::where('active', 1)->get();
        $deductions = Deduction::where('active', 1)->get();
        $activeTab = $request->query('tab', 'payroll');

        // Add distinct periods for revert dropdown
        $periods = Payroll::select('period')->distinct()->orderBy('period', 'desc')->pluck('period');

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
            'periods'
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
            // Query by payroll_id, case-insensitive
            $payroll = Payroll::with('employee')
                ->whereRaw('LOWER(payroll_id) = ?', [strtolower($id)])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'payroll' => [
                    'payroll_id' => $payroll->payroll_id,
                    'period' => $payroll->period,
                    'net_salary' => $payroll->net_salary,
                    'status' => $payroll->status,
                    'employee_name' => $payroll->employee_name ?? ($payroll->employee->name ?? 'N/A'),
                    'base_salary' => $payroll->base_salary,
                    'allowances' => $payroll->allowances,
                    'deductions' => $payroll->deductions,
                    'payment_method' => $payroll->payment_method,
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

        $transaction = Transaction::with('employee')->where('transaction_id', $id)->firstOrFail();
        return response()->json([
            'success' => true,
            'transaction' => [
                'transaction_id' => $transaction->transaction_id,
                'employee_name' => $transaction->employee_name ?? 'N/A',
                'amount' => $transaction->amount,
                'transaction_date' => $transaction->transaction_date,
                'status' => $transaction->status,
                'type' => $transaction->type,
                'payment_method' => $transaction->payment_method,
                'description' => $transaction->description,
            ]
        ]);
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

        $alert = PayrollAlert::with('employee')->where('alert_id', $id)->firstOrFail();
        return response()->json([
            'success' => true,
            'alert' => [
                'alert_id' => $alert->alert_id,
                'employee_name' => $alert->employee->name ?? 'N/A',
                'type' => $alert->type,
                'message' => $alert->message,
                'status' => $alert->status,
            ]
        ]);
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
                'message' => 'Unauthorized. Only Admin and HR can mark alerts as read.'
            ], 403);
        }

        $alert = PayrollAlert::where('alert_id', $id)->firstOrFail();
        $alert->update(['status' => 'Read']);

        return response()->json([
            'success' => true,
            'message' => 'Alert marked as read.'
        ]);
    }

    /**
     * Run payroll - Admin and HR only
     */
    public function run(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Admin and HR can run payroll.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payroll_period' => 'required|date_format:Y-m-d',
            'employee_selection' => 'required|in:all,single',
            'employee_id' => 'required_if:employee_selection,single|exists:employees,id',
            'nssf_rate' => 'required|numeric|min:0',
            'nhif_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $validator->errors()->all())
            ], 422);
        }

        $period = Carbon::parse($request->payroll_period)->startOfMonth();
        $employees = $request->employee_selection === 'all'
            ? Employee::where('status', 'active')->get()
            : Employee::where('id', $request->employee_id)->where('status', 'active')->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active employees found for payroll processing.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;

            foreach ($employees as $employee) {
                // Check if payroll already exists for this employee and period
                // ONLY check if we haven't reverted (i.e., don't prevent re-running after revert)
                $existingPayroll = Payroll::where('employee_id', $employee->id)
                    ->where('period', $period->format('Y-m'))
                    ->first();

                // If payroll exists and we're not in a revert scenario, prevent duplication
                if ($existingPayroll) {
                    // Check if this is a re-run after revert by looking for revert indicator
                    $wasReverted = PayrollAlert::where('employee_id', $employee->id)
                        ->where('type', 'like', '%Revert%')
                        ->where('created_at', '>=', now()->subHours(2)) // Reverted in last 2 hours
                        ->exists();

                    if (!$wasReverted) {
                        if ($request->employee_selection === 'single') {
                            return response()->json([
                                'success' => false,
                                'message' => 'Payroll already processed for this employee in this period. No need to repeat.'
                            ], 422);
                        }
                        // For 'all', skip
                        continue;
                    }
                }

                // Calculate salary components for new payroll
                $baseSalary = $employee->base_salary ?? 0;
                $allowances = $this->calculateAllowances($employee);
                $grossSalary = $baseSalary + array_sum(array_column($allowances, 'amount'));
                $deductions = $this->calculateDeductions($employee, $grossSalary, $request->nssf_rate, $request->nhif_rate);
                $netSalary = $grossSalary - array_sum(array_column($deductions, 'amount'));

                // Generate unique IDs using random strings
                $payrollId = $this->generateRandomId('PAY');
                $payslipId = $this->generateRandomId('PSLIP');
                $transactionId = $this->generateRandomId('TRX');

                Log::info('Generating payroll for employee', [
                    'employee_id' => $employee->id,
                    'payroll_id' => $payrollId,
                    'transaction_id' => $transactionId,
                    'period' => $period->format('Y-m'),
                ]);

                // Create payroll record
                Payroll::create([
                    'payroll_id' => $payrollId,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'period' => $period->format('Y-m'),
                    'base_salary' => $baseSalary,
                    'allowances' => array_sum(array_column($allowances, 'amount')),
                    'total_amount' => $grossSalary,
                    'deductions' => array_sum(array_column($deductions, 'amount')),
                    'net_salary' => $netSalary,
                    'status' => 'Processed',
                    'payment_method' => $employee->bank_name ? 'Bank Transfer' : null,
                    'created_by' => $user->id
                ]);

                // Create payslip
                Payslip::create([
                    'payslip_id' => $payslipId,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'period' => $period->format('Y-m'),
                    'base_salary' => $baseSalary,
                    'allowances' => array_sum(array_column($allowances, 'amount')),
                    'deductions' => array_sum(array_column($deductions, 'amount')),
                    'net_salary' => $netSalary,
                    'status' => 'Generated'
                ]);

                // Create transaction
                Transaction::create([
                    'transaction_id' => $transactionId,
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'type' => 'salary_payment',
                    'amount' => $netSalary,
                    'transaction_date' => now(),
                    'status' => 'Completed',
                    'payment_method' => $employee->bank_name ? 'Bank Transfer' : null,
                    'description' => "Salary payment for {$period->format('F Y')}"
                ]);

                // Check for alerts
                if (array_sum(array_column($deductions, 'amount')) / $grossSalary > 0.5) {
                    $alertId = $this->generateRandomId('ALRT');
                    PayrollAlert::create([
                        'alert_id' => $alertId,
                        'employee_id' => $employee->id,
                        'type' => 'High Deductions',
                        'message' => "Deductions for {$employee->name} exceed 50% of gross salary for {$period->format('F Y')}.",
                        'status' => 'Unread'
                    ]);
                }

                $processedCount++;
            }

            if ($processedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll already processed for all selected employees in this period.'
                ], 422);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Payroll processed successfully for ' . $processedCount . ' employees.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll processing failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'period' => $request->payroll_period,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payroll: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process retroactive pay - Admin and HR only
     */
    public function retro(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Admin and HR can process retroactive pay.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'retro_period' => 'required|date_format:Y-m-d',
            'employee_selection' => 'required|in:all,single',
            'employee_ids' => 'required_if:employee_selection,single|array',
            'employee_ids.*' => 'exists:employees,id',
            'adjustment_amount' => 'required|numeric|min:0',
            'adjustment_reason' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $validator->errors()->all())
            ], 422);
        }

        $period = Carbon::parse($request->retro_period)->startOfMonth();
        $employees = $request->employee_selection === 'all'
            ? Employee::where('status', 'active')->get()
            : Employee::whereIn('id', $request->employee_ids)->where('status', 'active')->get();

        if ($employees->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active employees found for retroactive pay processing.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $processedCount = 0;

            foreach ($employees as $employee) {
                $payroll = Payroll::where('employee_id', $employee->id)
                    ->where('period', $period->format('Y-m'))
                    ->first();

                if (!$payroll) {
                    if ($request->employee_selection === 'single') {
                        return response()->json([
                            'success' => false,
                            'message' => 'No payroll record found for this employee in the specified period.'
                        ], 422);
                    }
                    continue;
                }

                // Apply adjustment
                $payroll->allowances += $request->adjustment_amount;
                $payroll->total_amount += $request->adjustment_amount;
                $payroll->net_salary += $request->adjustment_amount; // Assuming adjustment is post-deduction
                $payroll->save();

                // Update related transaction
                $transaction = Transaction::where('employee_id', $employee->id)
                    ->where('type', 'salary_payment')
                    ->where('transaction_date', '>=', $period->startOfMonth())
                    ->where('transaction_date', '<', $period->endOfMonth()->addDay())
                    ->first();

                if ($transaction) {
                    $transaction->amount += $request->adjustment_amount;
                    $transaction->description .= " (Retroactive adjustment: {$request->adjustment_reason})";
                    $transaction->save();
                }

                // Create alert
                $alertId = $this->generateRandomId('ALRT');
                PayrollAlert::create([
                    'alert_id' => $alertId,
                    'employee_id' => $employee->id,
                    'type' => 'Retroactive Adjustment',
                    'message' => "Retroactive pay adjustment of TZS " . number_format($request->adjustment_amount, 0) . " applied for {$employee->name} in period {$period->format('F Y')}: {$request->adjustment_reason}",
                    'status' => 'Unread'
                ]);

                $processedCount++;
            }

            if ($processedCount === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payroll records found for the selected employees in this period.'
                ], 422);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Retroactive pay processed successfully for ' . $processedCount . ' employees.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Retroactive pay processing failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'period' => $request->retro_period,
                'amount' => $request->adjustment_amount,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process retroactive pay: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revert a specific payroll or payrolls for a period and optional employee - Admin and HR only
     */
    public function revert(Request $request, $payroll_id = null)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Only Admin and HR can revert payroll.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'period' => 'required_without:payroll_id|date_format:Y-m',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Handle period-based revert (with optional employee_id)
            if ($request->has('period') && !empty($request->period)) {
                $period = $request->period;
                $query = Payroll::where('period', $period);

                // If employee_id is provided, filter by employee
                if ($request->has('employee_id') && !empty($request->employee_id)) {
                    $query->where('employee_id', $request->employee_id);
                }

                $payrolls = $query->get();

                if ($payrolls->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No payroll records found for the specified period' . ($request->employee_id ? ' and employee.' : '.')
                    ], 404);
                }

                $revertedCount = 0;

                foreach ($payrolls as $payroll) {
                    $this->deleteRelatedRecords($payroll);
                    $payroll->delete();
                    
                    // Create revert alert to indicate this was reverted
                    $alertId = $this->generateRandomId('ALRT');
                    PayrollAlert::create([
                        'alert_id' => $alertId,
                        'employee_id' => $payroll->employee_id,
                        'type' => 'Payroll Reverted',
                        'message' => "Payroll {$payroll->payroll_id} for {$payroll->employee_name} in period {$period} has been reverted.",
                        'status' => 'Read' // Mark as read since it's system-generated
                    ]);
                    
                    $revertedCount++;
                }

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => "Successfully reverted {$revertedCount} payroll record(s) for period {$period}" . ($request->employee_id ? ' for the selected employee.' : '.')
                ]);
            }

            // Handle single payroll revert
            if (!$payroll_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll ID is required for single payroll revert.'
                ], 422);
            }

            // Find the specific payroll by payroll_id
            $payroll = Payroll::whereRaw('LOWER(payroll_id) = ?', [strtolower($payroll_id)])->first();
            if (!$payroll) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payroll record not found for the specified ID.'
                ], 404);
            }

            $this->deleteRelatedRecords($payroll);
            $payroll->delete();
            
            // Create revert alert
            $alertId = $this->generateRandomId('ALRT');
            PayrollAlert::create([
                'alert_id' => $alertId,
                'employee_id' => $payroll->employee_id,
                'type' => 'Payroll Reverted',
                'message' => "Payroll {$payroll_id} for {$payroll->employee_name} has been reverted.",
                'status' => 'Read'
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => "Payroll record {$payroll_id} has been reverted successfully."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payroll revert failed: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'payroll_id' => $payroll_id,
                'period' => $request->period ?? 'N/A',
                'employee_id' => $request->employee_id ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to revert payroll: ' . $e->getMessage()
            ], 500);
        }
    }
        public function revertAll(Request $request)
{
    $user = Auth::user();
    if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized. Only Admin and HR can revert all data.'
        ], 403);
    }

    $validator = Validator::make($request->all(), [
        'revert_types' => 'required|array',
        'revert_types.*' => 'in:payroll,transactions,alerts,retroactive',
        'period' => 'nullable|date_format:Y-m',
        'employee_id' => 'nullable|exists:employees,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => implode(', ', $validator->errors()->all())
        ], 422);
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

        // Revert Payroll Records
        if (in_array('payroll', $revertTypes)) {
            $payrollQuery = Payroll::where($queryConditions);
            $payrollCount = $payrollQuery->count();
            
            // Delete related records for each payroll first
            $payrolls = $payrollQuery->get();
            foreach ($payrolls as $payroll) {
                $this->deleteRelatedRecords($payroll);
            }
            
            $payrollQuery->delete();
            $revertedCount += $payrollCount;
            
            Log::info('Reverted payroll records', [
                'count' => $payrollCount,
                'period' => $period,
                'employee_id' => $employeeId,
                'user_id' => $user->id
            ]);
        }

        // Revert Transactions
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
            
            Log::info('Reverted transactions', [
                'count' => $transactionCount,
                'period' => $period,
                'employee_id' => $employeeId,
                'user_id' => $user->id
            ]);
        }

        // Revert Alerts
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
            
            Log::info('Reverted alerts', [
                'count' => $alertCount,
                'period' => $period,
                'employee_id' => $employeeId,
                'user_id' => $user->id
            ]);
        }

        // Revert Retroactive Payments (this would depend on your RetroactivePay model if it exists)
        if (in_array('retroactive', $revertTypes)) {
            // If you have a RetroactivePay model, uncomment and adjust this section
            /*
            $retroConditions = [];
            if ($employeeId) {
                $retroConditions[] = ['employee_id', '=', $employeeId];
            }
            if ($period) {
                $retroConditions[] = ['period', '=', $period];
            }
            
            $retroQuery = RetroactivePay::where($retroConditions);
            $retroCount = $retroQuery->count();
            $retroQuery->delete();
            $revertedCount += $retroCount;
            */
            
            // For now, we'll just log that retroactive was selected but not implemented
            Log::info('Retroactive revert selected but not implemented', [
                'period' => $period,
                'employee_id' => $employeeId,
                'user_id' => $user->id
            ]);
        }

        DB::commit();

        $message = "Successfully reverted {$revertedCount} records.";
        if ($period) {
            $message .= " Period: " . Carbon::parse($period)->format('F Y');
        }
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            $message .= $employee ? " Employee: " . $employee->name : "";
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Revert all data failed: ' . $e->getMessage(), [
            'user_id' => $user->id,
            'revert_types' => $revertTypes,
            'period' => $period,
            'employee_id' => $employeeId,
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to revert data: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Helper method to delete related records for a payroll
     */
    private function deleteRelatedRecords($payroll)
    {
        // Delete related payslips
        Payslip::where('employee_id', $payroll->employee_id)
            ->where('period', $payroll->period)
            ->delete();

        // Delete related transactions
        $descriptionPeriod = Carbon::parse($payroll->period)->format('F Y');
        Transaction::where('employee_id', $payroll->employee_id)
            ->where('transaction_date', '>=', Carbon::parse($payroll->period)->startOfMonth())
            ->where('transaction_date', '<', Carbon::parse($payroll->period)->endOfMonth()->addDay())
            ->where('description', 'like', '%' . $descriptionPeriod . '%')
            ->delete();

        // Delete related alerts (except revert alerts)
        PayrollAlert::where('employee_id', $payroll->employee_id)
            ->where('created_at', '>=', Carbon::parse($payroll->period)->startOfMonth())
            ->where('created_at', '<', Carbon::parse($payroll->period)->endOfMonth()->addDay())
            ->where('message', 'like', '%' . $descriptionPeriod . '%')
            ->where('type', 'not like', '%Revert%')
            ->delete();
    }

    /**
     * Export payroll to PDF
     */
    public function exportPDF(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'period' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $validator->errors()->all())
            ], 422);
        }

        $payrolls = Payroll::with('employee')
            ->where('period', $request->period)
            ->get();

        if ($payrolls->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payroll records found for the specified period.'
            ], 404);
        }

        $pdf = Pdf::loadView('payroll.pdf', compact('payrolls'));
        return $pdf->download('payroll-' . $request->period . '.pdf');
    }

    /**
     * Export payroll to Excel
     */
    public function exportExcel(Request $request)
    {
        $user = Auth::user();
        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'period' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => implode(', ', $validator->errors()->all())
            ], 422);
        }

        $payrolls = Payroll::with('employee')
            ->where('period', $request->period)
            ->get();

        if ($payrolls->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No payroll records found for the specified period.'
            ], 404);
        }

        return Excel::download(new class($payrolls) implements \Maatwebsite\Excel\Concerns\FromCollection {
            private $payrolls;

            public function __construct($payrolls)
            {
                $this->payrolls = $payrolls;
            }

            public function collection()
            {
                return $this->payrolls->map(function ($payroll) {
                    return [
                        'Payroll ID' => $payroll->payroll_id,
                        'Employee' => $payroll->employee_name,
                        'Period' => $payroll->period,
                        'Base Salary' => $payroll->base_salary,
                        'Allowances' => $payroll->allowances,
                        'Deductions' => $payroll->deductions,
                        'Net Salary' => $payroll->net_salary,
                        'Status' => $payroll->status,
                    ];
                });
            }
        }, 'payroll-' . $request->period . '.xlsx');
    }

    /**
     * Calculate allowances for an employee
     */
    private function calculateAllowances($employee)
    {
        $allowances = Allowance::where('active', 1)->get();
        $result = [];

        foreach ($allowances as $allowance) {
            $amount = $allowance->type === 'percentage'
                ? ($employee->base_salary * ($allowance->amount / 100))
                : $allowance->amount;

            $result[] = [
                'name' => $allowance->name,
                'amount' => $amount,
                'taxable' => $allowance->taxable
            ];
        }

        return $result;
    }

    /**
     * Calculate deductions based on Tanzanian regulations
     */
    private function calculateDeductions($employee, $grossSalary, $nssfRate = null, $nhifRate = null)
    {
        $settings = $this->getSettings();
        $deductions = Deduction::where('active', 1)->get();
        $result = [];

        $nssfRate = $nssfRate ?? ($settings['nssf_employee_rate'] ?? 10.0);
        $nssf = $grossSalary * ($nssfRate / 100);
        $result[] = ['name' => 'NSSF', 'amount' => $nssf, 'category' => 'statutory'];

        $nhifRate = $nhifRate ?? $this->getNHIFRate($grossSalary);
        $nhif = $grossSalary * ($nhifRate / 100);
        $result[] = ['name' => 'NHIF', 'amount' => $nhif, 'category' => 'statutory'];

        $taxableIncome = $grossSalary - $nssf;
        $paye = $this->calculatePAYE($taxableIncome);
        $result[] = ['name' => 'PAYE', 'amount' => $paye, 'category' => 'statutory'];

        foreach ($deductions as $deduction) {
            $amount = $deduction->type === 'percentage'
                ? ($grossSalary * ($deduction->amount / 100))
                : $deduction->amount;

            $result[] = [
                'name' => $deduction->name,
                'amount' => $amount,
                'category' => $deduction->category
            ];
        }

        return $result;
    }

    /**
     * Calculate NHIF rate based on gross salary
     */
    private function getNHIFRate($grossSalary)
    {
        // Flat 3% employee rate as per 2025 regulations (total 6%, shared with employer)
        return 3.0;
    }

    /**
     * Calculate PAYE based on Tanzanian tax brackets
     */
    private function calculatePAYE($taxableIncome)
    {
        $settings = $this->getSettings();
        $taxFreeThreshold = $settings['paye_tax_free'] ?? 270000;

        // 2025 Tanzanian tax brackets (TZS, monthly) - confirmed no changes from previous years
        if ($taxableIncome <= $taxFreeThreshold) {
            return 0;
        } elseif ($taxableIncome <= 520000) {
            return ($taxableIncome - $taxFreeThreshold) * 0.09;
        } elseif ($taxableIncome <= 760000) {
            return 22500 + ($taxableIncome - 520000) * 0.20;
        } elseif ($taxableIncome <= 1000000) {
            return 70500 + ($taxableIncome - 760000) * 0.25;
        } else {
            return 130500 + ($taxableIncome - 1000000) * 0.30;
        }
    }

    /**
     * Retrieve global settings (Mock implementation)
     */
    private function getSettings()
    {
        // In a real application, this would fetch from a database table like 'settings'
        return Setting::pluck('value', 'key')->toArray();
    }

    /**
     * Generate random ID with prefix (more collision-resistant)
     */
    private function generateRandomId($prefix)
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            // Generate random string (8 characters) using more entropy
            $random = strtoupper(bin2hex(random_bytes(4))); // 8 characters
            $newId = $prefix . '-' . $random;

            // Check if ID is unique across all relevant tables
            $isUnique = !Payroll::where('payroll_id', $newId)->exists() &&
                       !Transaction::where('transaction_id', $newId)->exists() &&
                       !PayrollAlert::where('alert_id', $newId)->exists() &&
                       !Payslip::where('payslip_id', $newId)->exists();

            if ($isUnique) {
                return $newId;
            }

            $attempt++;
            Log::warning("Random ID collision detected for {$newId}, attempt {$attempt}");
        }

        // Fallback: use timestamp-based ID if random generation fails
        $timestamp = now()->format('YmdHis');
        $fallbackId = $prefix . '-' . $timestamp . '-' . rand(1000, 9999);
        Log::warning("Using fallback ID generation: {$fallbackId}");
        return $fallbackId;
    }
}