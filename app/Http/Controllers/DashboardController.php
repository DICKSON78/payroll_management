<?php
namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\ComplianceTask;
use App\Models\Department;
use App\Models\Bank;
use App\Models\ComplianceType;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Check if the user is an employee and abort if true
        if (strtolower($user->role) === 'employee') {
            abort(404);
        }

        // The rest of your dashboard code remains the same
        // Total employees
        $totalEmployees = strtolower($user->role) !== 'employee' ? Employee::count() : 1;

        // Employee growth (admin/HR only)
        $employeeGrowth = strtolower($user->role) !== 'employee' ? $this->calculateGrowth(Employee::class, 'created_at') : 0;

        // Monthly payroll
        $activeEmployees = strtolower($user->role) !== 'employee' ? Employee::where('status', 'active')->get() : collect([Auth::user()->employee]);
        $monthlyPayroll = $activeEmployees->sum('base_salary');

        // Payroll growth (admin/HR only)
        $payrollGrowth = strtolower($user->role) !== 'employee' ? $this->calculateGrowth(Payroll::class, 'created_at') : 0;

        // Payslips generated
        $payslipsGenerated = strtolower($user->role) !== 'employee'
            ? Payroll::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count()
            : Payroll::where('employee_id', Auth::user()->employee->id ?? 0)
                ->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count();

        // Pending compliance tasks
        $pendingTasks = 0;
        try {
            if (class_exists(ComplianceTask::class) && Schema::hasTable('compliance_tasks')) {
                $pendingTasks = strtolower($user->role) === 'employee'
                    ? ComplianceTask::where('employee_id', Auth::user()->employee->id ?? 0)
                        ->where('status', 'Pending')
                        ->count()
                    : ComplianceTask::where('status', 'Pending')->count();
            } else {
                \Log::warning('ComplianceTask model or table not found.');
            }
        } catch (\Exception $e) {
            \Log::error('Error calculating pending tasks: ' . $e->getMessage());
        }

        // Recent payslips
        $recentPayslips = strtolower($user->role) === 'employee'
            ? Payroll::with('employee')->where('employee_id', Auth::user()->employee->id ?? 0)->latest()->take(5)->get()
            : Payroll::with('employee')->latest()->take(5)->get();

        // Employees list (admin/HR only)
        $employees = strtolower($user->role) !== 'employee' ? Employee::with('user')->get() : collect([Auth::user()->employee]);

        // Prepare the employee data for CSV export
        $employeesForExport = $employees->map(function($employee) {
            return [
                'id' => $employee->employee_id ?? '',
                'name' => $employee->name ?? '',
                'department' => $employee->department ?? '',
                'position' => $employee->position ?? '',
                'gross_salary' => ($employee->base_salary ?? 0) + ($employee->allowances ?? 0),
                'status' => $employee->status ?? ''
            ];
        })->toArray();

        // Fetch departments, banks, and compliance types
        $departments = Department::all();
        $banks = Bank::all();
        $complianceTypes = ComplianceType::all();

        $chartLabels = $this->getChartLabels($user);
        $chartData = $this->getChartData($user);
        $currentPeriod = Carbon::now()->format('F Y');

        return view('dashboard.dashboard', compact(
            'totalEmployees', 'employeeGrowth', 'monthlyPayroll', 'payrollGrowth',
            'payslipsGenerated', 'pendingTasks', 'recentPayslips', 'employees',
            'employeesForExport',
            'chartLabels', 'chartData', 'currentPeriod', 'departments', 'banks', 'complianceTypes'
        ));
    }

    private function calculateGrowth($model, $dateColumn)
    {
        $currentPeriod = $model::whereMonth($dateColumn, Carbon::now()->month)
            ->whereYear($dateColumn, Carbon::now()->year)
            ->count();
        $previousPeriod = $model::whereMonth($dateColumn, Carbon::now()->subMonth()->month)
            ->whereYear($dateColumn, Carbon::now()->subMonth()->year)
            ->count();
        return $previousPeriod == 0 ? ($currentPeriod > 0 ? 100 : 0) : round((($currentPeriod - $previousPeriod) / $previousPeriod) * 100, 2);
    }

    private function getChartLabels($user)
    {
        return collect(range(5, -1, -1))->map(function ($month) {
            return Carbon::now()->subMonths($month)->format('M Y');
        })->toArray();
    }

    private function getChartData($user)
    {
        return collect(range(5, -1, -1))->map(function ($month) use ($user) {
            $query = Payroll::whereMonth('created_at', Carbon::now()->subMonths($month)->month)
                ->whereYear('created_at', Carbon::now()->subMonths($month)->year);
            if (strtolower($user->role) === 'employee') {
                $query->where('employee_id', Auth::user()->employee->id ?? 0);
            }
            return $query->sum('total_amount') / 1000000; // Convert to millions for chart
        })->toArray();
    }
}