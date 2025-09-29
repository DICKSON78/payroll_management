<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\ComplianceTask;
use App\Models\Department;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use Illuminate\Support\Collection;

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

        // Recent payslips (last 5)
        $recentPayslips = Payslip::with('employee')->latest()->take(5)->get();

        // Employees data
        $employees = Employee::with('departmentRel')->get();
        $employeesForExport = $employees->map(function ($e) {
            return [
                'id' => $e->employee_id ?? '',
                'name' => $e->name ?? '',
                'department' => $e->departmentRel->name ?? $e->department ?? 'N/A',
                'position' => $e->position ?? '',
                'gross_salary' => $e->base_salary ?? 0,
                'status' => $e->status ?? '',
            ];
        });

        // Default chart data for last 6 months
        $chartLabels = $this->getChartLabels(6);
        $chartData = $this->getChartData(6, $user, $isAdminOrHR);

        // Common settings
        $currentPeriod = now()->format('F Y');
        $settings = Setting::pluck('value', 'key')->toArray();

        // Employee dashboard (limited)
        if ($isEmployee && !$isAdminOrHR) {
            $totalEmployees = 1;
            $employeeGrowth = 0;
            $monthlyPayroll = $user->base_salary ?? 0;
            $payrollGrowth = 0;

            $payslipsGenerated = Payslip::where('employee_id', $user->id)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

            $pendingTasks = 0;
            try {
                if (class_exists(ComplianceTask::class) && Schema::hasTable('compliance_tasks')) {
                    $pendingTasks = ComplianceTask::where('employee_id', $user->id)
                        ->where('status', 'pending')
                        ->count();
                }
            } catch (\Exception $e) {
                $pendingTasks = 0;
            }

            return view('dashboard.dashboard', compact(
                'totalEmployees', 'monthlyPayroll', 'employeeGrowth',
                'payslipsGenerated', 'pendingTasks', 'settings', 'currentPeriod',
                'isAdminOrHR', 'payrollGrowth', 'recentPayslips',
                'employees', 'employeesForExport', 'chartLabels', 'chartData'
            ));
        }

        // Admin/HR dashboard
        $totalEmployees = Employee::count();
        $employeeGrowth = $this->calculateGrowth(Employee::class, 'created_at');
        $monthlyPayroll = Payslip::whereMonth('period', Carbon::now()->month)
                                 ->whereYear('period', Carbon::now()->year)
                                 ->sum('net_salary');
        $payrollGrowth = $this->calculateGrowth(Payslip::class, 'period');
        $payslipsGenerated = Payslip::whereMonth('period', Carbon::now()->month)
                                    ->whereYear('period', Carbon::now()->year)
                                    ->count();
        $pendingTasks = ComplianceTask::where('status', 'pending')->count();

        $departments = Department::all();
        $banks = Bank::all();

        // Report types from ReportController logic
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

        // Compliance types from ComplianceController logic
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
            'employees', 'employeesForExport', 'chartLabels', 'chartData'
        ));
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
                $payslipsGenerated = Payslip::where('employee_id', $user->id)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year)
                    ->count();
                $pendingTasks = 0;
                try {
                    if (class_exists(ComplianceTask::class) && Schema::hasTable('compliance_tasks')) {
                        $pendingTasks = ComplianceTask::where('employee_id', $user->id)
                            ->where('status', 'pending')
                            ->count();
                    }
                } catch (\Exception $e) {
                    $pendingTasks = 0;
                }
            } else {
                $totalEmployees = Employee::count();
                $employeeGrowth = $this->calculateGrowth(Employee::class, 'created_at');
                $monthlyPayroll = Payslip::whereMonth('period', Carbon::now()->month)
                                         ->whereYear('period', Carbon::now()->year)
                                         ->sum('net_salary');
                $payrollGrowth = $this->calculateGrowth(Payslip::class, 'period');
                $payslipsGenerated = Payslip::whereMonth('period', Carbon::now()->month)
                                            ->whereYear('period', Carbon::now()->year)
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
                    $q->where('employee_id', $user->id);
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
                    $q->where('employee_id', $user->id);
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
                    $q->where('employee_id', $user->id);
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
}