<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\ComplianceTask;
use App\Models\Department;
use App\Models\Bank;
use App\Models\Role;
use App\Models\Allowance;
use App\Models\Deduction;
use App\Models\Transaction;
use App\Models\PayrollAlert;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            \Log::warning('User not authenticated.');
            return redirect('/login')->with('error', 'Please log in.');
        }

        $isAdminOrHR = in_array(strtolower($user->role ?? ''), ['admin', 'hr']);
        $isEmployee = strtolower($user->role ?? '') === 'employee';

        // Recent payslips (last 5) with complete employee data
        $recentPayslips = Payslip::select(
                'payslips.*',
                'employees.department',
                'employees.position',
                'employees.email'
            )
            ->leftJoin('employees', 'payslips.employee_id', '=', 'employees.employee_id')
            ->latest()
            ->take(5)
            ->get();

        // Employees data with complete information
        $employees = Employee::with('departmentRel')->get();
        $employeesForExport = $employees->map(function ($e) {
            return [
                'id' => $e->employee_id ?? '',
                'name' => $e->name ?? '',
                'department' => $e->departmentRel->name ?? $e->department ?? 'N/A',
                'position' => $e->position ?? '',
                'email' => $e->email ?? '',
                'phone' => $e->phone ?? '',
                'base_salary' => $e->base_salary ?? 0,
                'status' => $e->status ?? '',
                'hire_date' => $e->hire_date ?? '',
                'employment_type' => $e->employment_type ?? '',
            ];
        });

        // Default chart data for last 6 months
        $chartLabels = $this->getChartLabels(6);
        $chartData = $this->getChartData(6, $user, $isAdminOrHR);

        // Common settings
        $currentPeriod = now()->format('F Y');
        $settings = Setting::pluck('value', 'key')->toArray();

        // Fetch all required data for modals and dashboard
        $departments = Department::all();
        $banks = Bank::all();
        $allowances = Allowance::where('active', 1)->get();
        $deductions = Deduction::where('active', 1)->get();
        $roles = Role::all();

        // Employee dashboard (limited view)
        if ($isEmployee && !$isAdminOrHR) {
            $totalEmployees = 1;
            $employeeGrowth = 0;
            $monthlyPayroll = $user->base_salary ?? 0;
            $payrollGrowth = 0;

            $payslipsGenerated = Payslip::where('employee_id', $user->employee_id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $pendingTasks = 0;
            try {
                if (class_exists(ComplianceTask::class) && Schema::hasTable('compliance_tasks')) {
                    $pendingTasks = ComplianceTask::where('employee_id', $user->employee_id)
                        ->where('status', 'pending')
                        ->count();
                }
            } catch (\Exception $e) {
                $pendingTasks = 0;
            }

            // Additional stats for employee dashboard
            $recentTransactions = Transaction::where('employee_id', $user->employee_id)
                ->orderBy('transaction_date', 'desc')
                ->take(5)
                ->get();

            $attendanceStats = $this->getEmployeeAttendanceStats($user->employee_id);

            return view('dashboard.dashboard', compact(
                'totalEmployees', 'monthlyPayroll', 'employeeGrowth',
                'payslipsGenerated', 'pendingTasks', 'settings', 'currentPeriod',
                'isAdminOrHR', 'payrollGrowth', 'recentPayslips',
                'employees', 'employeesForExport', 'chartLabels', 'chartData',
                'departments', 'banks', 'allowances', 'roles', 'deductions',
                'recentTransactions', 'attendanceStats'
            ));
        }

        // Admin/HR dashboard - Complete statistics
        $totalEmployees = Employee::count();
        $employeeGrowth = $this->calculateGrowth(Employee::class, 'created_at');
        
        $monthlyPayroll = Payroll::whereMonth('created_at', Carbon::now()->month)
                                 ->whereYear('created_at', Carbon::now()->year)
                                 ->sum('net_salary');
        $payrollGrowth = $this->calculatePayrollGrowth();
        
        $payslipsGenerated = Payslip::whereMonth('created_at', Carbon::now()->month)
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->count();
        
        $pendingTasks = ComplianceTask::where('status', 'pending')->count();

        // Additional statistics for admin dashboard
        $totalDepartments = Department::count();
        $activePayrolls = Payroll::where('status', 'processed')->count();
        $recentTransactions = Transaction::orderBy('transaction_date', 'desc')->take(5)->get();
        $unreadAlerts = PayrollAlert::where('status', 'unread')->count();
        $pendingLeaveRequests = LeaveRequest::where('status', 'pending')->count();

        // Department-wise employee count
        $departmentStats = Employee::select('department', \DB::raw('COUNT(*) as count'))
            ->groupBy('department')
            ->get();

        // Report types from schema
        $reportTypes = collect([
            'payslip' => 'Payslip',
            'payroll_summary' => 'Payroll Summary',
            'tax_report' => 'Tax Report',
            'nssf_report' => 'NSSF Report',
            'nhif_report' => 'NHIF Report',
            'wcf_report' => 'WCF Report',
            'sdl_report' => 'SDL Report',
            'year_end_summary' => 'Year End Summary'
        ])->map(function ($name, $type) {
            return (object) ['type' => $type, 'name' => $name];
        });

        // Compliance types from schema
        $complianceTypes = collect([
            'PAYE' => 'PAYE',
            'NSSF' => 'NSSF',
            'NHIF' => 'NHIF',
            'WCF' => 'WCF',
            'SDL' => 'SDL'
        ])->map(function ($name, $type) {
            return (object) ['type' => $type, 'name' => $name];
        });

        return view('dashboard.dashboard', compact(
            'totalEmployees', 'monthlyPayroll', 'employeeGrowth', 'payslipsGenerated',
            'pendingTasks', 'settings', 'currentPeriod', 'isAdminOrHR', 'payrollGrowth',
            'recentPayslips', 'departments', 'banks', 'complianceTypes', 'reportTypes',
            'employees', 'employeesForExport', 'chartLabels', 'chartData',
            'allowances', 'roles', 'deductions', 'totalDepartments', 'activePayrolls',
            'recentTransactions', 'unreadAlerts', 'pendingLeaveRequests', 'departmentStats'
        ));
    }

    /**
     * Handle Quick Actions from Dashboard
     */
    public function quickActions(Request $request)
    {
        $user = Auth::user();
        $action = $request->input('action');

        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        try {
            switch ($action) {
                case 'quick_add_employee':
                    return $this->quickAddEmployee($request->all());
                    
                case 'quick_run_payroll':
                    return $this->quickRunPayroll($request->all());
                    
                case 'quick_generate_payslip':
                    return $this->quickGeneratePayslip($request->all());
                    
                case 'quick_add_compliance':
                    return $this->quickAddCompliance($request->all());
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid action specified.'
                    ], 400);
            }
        } catch (\Exception $e) {
            \Log::error('Quick action failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Action failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick Add Employee - Simplified version
     */
    private function quickAddEmployee($data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'position' => 'required|string|max:255',
            'department' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            DB::beginTransaction();

            $employeeId = $this->generateUniqueEmployeeId();
            $nameParts = explode(' ', trim($data['name']));
            $lastName = end($nameParts);
            $initialPassword = strtolower($lastName ?: 'employee123');

            $employeeData = [
                'employee_id' => $employeeId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($initialPassword),
                'department' => $data['department'],
                'position' => $data['position'],
                'role' => 'employee',
                'base_salary' => $data['base_salary'],
                'employment_type' => 'full-time',
                'hire_date' => now(),
                'status' => 'active',
                'allowances' => 0.00,
                'deductions' => 0.00,
            ];

            // Add optional fields if provided
            $optionalFields = ['phone', 'gender', 'bank_name', 'account_number'];
            foreach ($optionalFields as $field) {
                if (!empty($data[$field])) {
                    $employeeData[$field] = $data[$field];
                }
            }

            $employee = Employee::create($employeeData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Employee added successfully! ID: ' . $employeeId . ', Initial Password: ' . $initialPassword,
                'employee_id' => $employeeId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Quick Run Payroll
     */
    private function quickRunPayroll($data)
    {
        $validator = Validator::make($data, [
            'period' => 'required|date_format:Y-m',
            'employee_selection' => 'required|in:all,single,multiple',
            'nssf_rate' => 'required|numeric|min:0|max:100',
            'nhif_rate' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            $period = $data['period'];
            $periodDisplay = Carbon::parse($period . '-01')->format('F Y');
            
            // Determine employees based on selection
            $employees = collect();
            
            if ($data['employee_selection'] === 'all') {
                $employees = Employee::where('status', 'active')->get();
            } elseif ($data['employee_selection'] === 'single' && !empty($data['employee_id'])) {
                $employee = Employee::where('employee_id', $data['employee_id'])->where('status', 'active')->first();
                if ($employee) {
                    $employees = collect([$employee]);
                }
            } elseif ($data['employee_selection'] === 'multiple' && !empty($data['employee_ids'])) {
                $employees = Employee::whereIn('employee_id', $data['employee_ids'])->where('status', 'active')->get();
            }

            if ($employees->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active employees found for payroll processing.'
                ], 404);
            }

            DB::beginTransaction();
            $processedCount = 0;

            foreach ($employees as $employee) {
                // Check if payroll already exists for this period
                $existingPayroll = Payroll::where('employee_id', $employee->employee_id)
                    ->where('period', $period)
                    ->first();

                if (!$existingPayroll) {
                    $baseSalary = $employee->base_salary ?? 0;
                    $allowances = $employee->allowances ?? 0;
                    $grossSalary = $baseSalary + $allowances;

                    // Calculate deductions
                    $nssf = $grossSalary * ($data['nssf_rate'] / 100);
                    $nhif = $this->calculateNHIF($grossSalary);
                    $paye = $this->calculatePAYE($grossSalary - $nssf);
                    
                    $totalDeductions = $nssf + $nhif + $paye;
                    $netSalary = $grossSalary - $totalDeductions;

                    // Create payroll record
                    Payroll::create([
                        'payroll_id' => 'PAY-' . strtoupper(Str::random(8)),
                        'employee_id' => $employee->employee_id,
                        'employee_name' => $employee->name,
                        'period' => $period,
                        'base_salary' => $baseSalary,
                        'allowances' => $allowances,
                        'deductions' => $totalDeductions,
                        'net_salary' => $netSalary,
                        'total_amount' => $grossSalary,
                        'status' => 'Processed',
                        'payment_method' => $employee->bank_name ? 'Bank Transfer' : 'Cash',
                    ]);

                    $processedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payroll processed for ' . $processedCount . ' employees for ' . $periodDisplay,
                'processed_count' => $processedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Quick Generate Payslip
     */
    private function quickGeneratePayslip($data)
    {
        $validator = Validator::make($data, [
            'employee_id' => 'required|exists:employees,employee_id',
            'period' => 'required|date_format:Y-m',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            $employee = Employee::where('employee_id', $data['employee_id'])->first();
            $period = $data['period'];

            // Check if payslip already exists
            $existingPayslip = Payslip::where('employee_id', $data['employee_id'])
                ->where('period', $period)
                ->first();

            if ($existingPayslip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip for this period already exists.'
                ], 409);
            }

            $baseSalary = $employee->base_salary ?? 0;
            $allowances = $employee->allowances ?? 0;
            $grossSalary = $baseSalary + $allowances;

            // Calculate deductions using default rates
            $nssfRate = 10.0; // Default NSSF rate
            $nssf = $grossSalary * ($nssfRate / 100);
            $nhif = $this->calculateNHIF($grossSalary);
            $paye = $this->calculatePAYE($grossSalary - $nssf);
            
            $totalDeductions = $nssf + $nhif + $paye;
            $netSalary = $grossSalary - $totalDeductions;

            // Create payslip
            $payslip = Payslip::create([
                'payslip_id' => 'PSL-' . strtoupper(Str::random(8)),
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->name,
                'period' => $period,
                'base_salary' => $baseSalary,
                'allowances' => $allowances,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'status' => 'Generated',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payslip generated successfully for ' . $employee->name,
                'payslip_id' => $payslip->payslip_id
            ]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Quick Add Compliance Task
     */
    private function quickAddCompliance($data)
    {
        $validator = Validator::make($data, [
            'type' => 'required|in:PAYE,NSSF,NHIF,WCF,SDL',
            'due_date' => 'required|date|after_or_equal:today',
            'employee_id' => 'nullable|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validator->errors()->all())
            ], 422);
        }

        try {
            $complianceTask = ComplianceTask::create([
                'task_id' => 'CMP-' . strtoupper(Str::random(8)),
                'type' => $data['type'],
                'employee_id' => $data['employee_id'] ?? null,
                'due_date' => $data['due_date'],
                'amount' => $data['amount'] ?? null,
                'details' => $data['details'] ?? 'Quick compliance task created from dashboard',
                'status' => 'Pending',
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Compliance task created successfully',
                'task_id' => $complianceTask->task_id
            ]);

        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Generate unique employee ID
     */
    private function generateUniqueEmployeeId()
    {
        $prefix = "EMP";
        do {
            $randomPart = strtoupper(Str::random(8));
            $newId = $prefix . '-' . $randomPart;
        } while (Employee::where('employee_id', $newId)->exists());

        return $newId;
    }

    /**
     * Calculate NHIF amount
     */
    private function calculateNHIF($salary)
    {
        $tiers = [
            5000 => 150, 6000 => 300, 8000 => 400, 10000 => 500,
            12000 => 600, 15000 => 750, 20000 => 850, 25000 => 900,
            30000 => 950, 35000 => 1000, 40000 => 1100, 45000 => 1200,
            50000 => 1300, 60000 => 1400, 70000 => 1500, 80000 => 1600,
            90000 => 1700, 100000 => 1800, PHP_INT_MAX => 2000
        ];

        foreach ($tiers as $limit => $amount) {
            if ($salary <= $limit) {
                return $amount;
            }
        }
        return 2000;
    }

    /**
     * Calculate PAYE tax
     */
    private function calculatePAYE($taxableIncome)
    {
        if ($taxableIncome <= 270000) return 0;
        elseif ($taxableIncome <= 520000) return ($taxableIncome - 270000) * 0.08;
        elseif ($taxableIncome <= 760000) return 20000 + ($taxableIncome - 520000) * 0.20;
        elseif ($taxableIncome <= 1000000) return 68000 + ($taxableIncome - 760000) * 0.25;
        else return 128000 + ($taxableIncome - 1000000) * 0.30;
    }

    public function getDashboardData(Request $request)
    {
        try {
            $user = Auth::user();
            $isAdminOrHR = in_array(strtolower($user->role ?? ''), ['admin', 'hr']);
            $isEmployee = strtolower($user->role ?? '') === 'employee';

            if ($isEmployee && !$isAdminOrHR) {
                $totalEmployees = 1;
                $employeeGrowth = 0;
                $monthlyPayroll = $user->base_salary ?? 0;
                $payrollGrowth = 0;
                $payslipsGenerated = Payslip::where('employee_id', $user->employee_id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count();
                $pendingTasks = 0;
                try {
                    if (class_exists(ComplianceTask::class) && Schema::hasTable('compliance_tasks')) {
                        $pendingTasks = ComplianceTask::where('employee_id', $user->employee_id)
                            ->where('status', 'pending')
                            ->count();
                    }
                } catch (\Exception $e) {
                    $pendingTasks = 0;
                }
            } else {
                $totalEmployees = Employee::count();
                $employeeGrowth = $this->calculateGrowth(Employee::class, 'created_at');
                $monthlyPayroll = Payroll::whereMonth('created_at', Carbon::now()->month)
                                         ->whereYear('created_at', Carbon::now()->year)
                                         ->sum('net_salary');
                $payrollGrowth = $this->calculatePayrollGrowth();
                $payslipsGenerated = Payslip::whereMonth('created_at', Carbon::now()->month)
                                            ->whereYear('created_at', Carbon::now()->year)
                                            ->count();
                $pendingTasks = ComplianceTask::where('status', 'pending')->count();
            }

            return response()->json([
                'totalEmployees' => $totalEmployees,
                'employeeGrowth' => $employeeGrowth,
                'monthlyPayroll' => $monthlyPayroll,
                'payrollGrowth' => $payrollGrowth,
                'payslipsGenerated' => $payslipsGenerated,
                'pendingTasks' => $pendingTasks,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Dashboard data fetch failed: ' . $e->getMessage());
            return response()->json(['error' => 'Server error occurred'], 500);
        }
    }

    public function getPayrollData(Request $request)
    {
        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role ?? ''), ['admin', 'hr']);
        $period = $request->get('period', 6);

        return response()->json([
            'labels' => $this->getChartLabels($period),
            'values' => $this->getChartData($period, $user, $isAdminOrHR),
        ]);
    }

    private function getChartLabels($period)
    {
        if ($period == 6) {
            return collect(range(5, 0, -1))->map(fn($m) => Carbon::now()->subMonths($m)->format('M Y'))->toArray();
        }
        if ($period == 12) {
            $startOfYear = Carbon::now()->startOfYear();
            return collect(range(0, 11))->map(fn($m) => $startOfYear->copy()->addMonths($m)->format('M Y'))->toArray();
        }
        if ($period == 24) {
            $startOfLastYear = Carbon::now()->subYear()->startOfYear();
            return collect(range(0, 11))->map(fn($m) => $startOfLastYear->copy()->addMonths($m)->format('M Y'))->toArray();
        }
        return [];
    }

    private function getChartData($period, $user, $isAdminOrHR)
    {
        $data = [];
        if ($period == 6) {
            $data = collect(range(5, 0, -1))->map(function ($m) use ($user, $isAdminOrHR) {
                $q = Payroll::whereMonth('created_at', Carbon::now()->subMonths($m)->month)
                            ->whereYear('created_at', Carbon::now()->subMonths($m)->year);
                if (!$isAdminOrHR) {
                    $q->where('employee_id', $user->employee_id);
                }
                return round($q->sum('total_amount') / 1_000_000, 2);
            })->toArray();
        } elseif ($period == 12) {
            $startOfYear = Carbon::now()->startOfYear();
            $data = collect(range(0, 11))->map(function ($m) use ($startOfYear, $user, $isAdminOrHR) {
                $date = $startOfYear->copy()->addMonths($m);
                $q = Payroll::whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year);
                if (!$isAdminOrHR) {
                    $q->where('employee_id', $user->employee_id);
                }
                return round($q->sum('total_amount') / 1_000_000, 2);
            })->toArray();
        } elseif ($period == 24) {
            $startOfLastYear = Carbon::now()->subYear()->startOfYear();
            $data = collect(range(0, 11))->map(function ($m) use ($startOfLastYear, $user, $isAdminOrHR) {
                $date = $startOfLastYear->copy()->addMonths($m);
                $q = Payroll::whereMonth('created_at', $date->month)
                            ->whereYear('created_at', $date->year);
                if (!$isAdminOrHR) {
                    $q->where('employee_id', $user->employee_id);
                }
                return round($q->sum('total_amount') / 1_000_000, 2);
            })->toArray();
        }
        return $data;
    }

    private function calculateGrowth($model, $dateColumn)
    {
        $current = $model::whereMonth($dateColumn, Carbon::now()->month)
                         ->whereYear($dateColumn, Carbon::now()->year)
                         ->count();

        $previous = $model::whereMonth($dateColumn, Carbon::now()->subMonth()->month)
                          ->whereYear($dateColumn, Carbon::now()->subMonth()->year)
                          ->count();

        return $previous == 0
            ? ($current > 0 ? 100 : 0)
            : round((($current - $previous) / $previous) * 100, 2);
    }

    private function calculatePayrollGrowth()
    {
        $current = Payroll::whereMonth('created_at', Carbon::now()->month)
                         ->whereYear('created_at', Carbon::now()->year)
                         ->sum('net_salary');

        $previous = Payroll::whereMonth('created_at', Carbon::now()->subMonth()->month)
                          ->whereYear('created_at', Carbon::now()->subMonth()->year)
                          ->sum('net_salary');

        return $previous == 0
            ? ($current > 0 ? 100 : 0)
            : round((($current - $previous) / $previous) * 100, 2);
    }

    private function getEmployeeAttendanceStats($employeeId)
    {
        try {
            $currentMonth = Carbon::now()->month;
            $currentYear = Carbon::now()->year;

            $presentDays = Attendance::where('employee_id', $employeeId)
                ->whereMonth('date', $currentMonth)
                ->whereYear('date', $currentYear)
                ->where('status', 'Present')
                ->count();

            $totalWorkingDays = Carbon::now()->daysInMonth;

            return [
                'present_days' => $presentDays,
                'total_days' => $totalWorkingDays,
                'attendance_rate' => $totalWorkingDays > 0 ? round(($presentDays / $totalWorkingDays) * 100, 2) : 0
            ];
        } catch (\Exception $e) {
            return [
                'present_days' => 0,
                'total_days' => 0,
                'attendance_rate' => 0
            ];
        }
    }
}