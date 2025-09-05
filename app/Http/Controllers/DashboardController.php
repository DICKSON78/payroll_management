<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payslip;
use App\Models\ComplianceTask;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Fetch all employees
        $employees = Employee::all();

        // Fetch recent payslips with employee relationship
        $recentPayslips = Payslip::with('employee')
            ->latest()
            ->take(5)
            ->get();

        // Calculate dashboard metrics
        $totalEmployees = $employees->count();
        $currentPeriod = Carbon::now()->format('F Y');
        $currentMonth = Carbon::now()->format('Y-m');

        // Calculate monthly payroll (sum of gross_salary for current period)
        $monthlyPayroll = Payslip::where('period', $currentMonth)
            ->sum('gross_salary') / 1_000_000; // In millions (TZS)

        // Count payslips generated for the current period
        $payslipsGenerated = Payslip::where('period', $currentMonth)->count();

        // Count pending compliance tasks
        $pendingTasks = ComplianceTask::where('status', 'Pending')->count();

        // Calculate employee growth (current vs. previous month)
        $currentMonthEmployees = Employee::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
        $lastMonthEmployees = Employee::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->count();
        $employeeGrowth = $lastMonthEmployees > 0
            ? round((($currentMonthEmployees - $lastMonthEmployees) / $lastMonthEmployees) * 100, 2)
            : 0;

        // Calculate payroll growth (current vs. previous month)
        $currentMonthPayroll = Payslip::where('period', $currentMonth)
            ->sum('gross_salary') / 1_000_000;
        $lastMonthPayroll = Payslip::where('period', Carbon::now()->subMonth()->format('Y-m'))
            ->sum('gross_salary') / 1_000_000;
        $payrollGrowth = $lastMonthPayroll > 0
            ? round((($currentMonthPayroll - $lastMonthPayroll) / $lastMonthPayroll) * 100, 2)
            : 0;

        // Chart data for payroll overview (last 6 months)
        $chartData = Payslip::select(
            DB::raw('period as month'),
            DB::raw('SUM(gross_salary) / 1000000 as total')
        )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->take(6)
            ->get()
            ->sortBy('month') // Sort in ascending order for chart
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->month)->format('M') => $item->total];
            });

        $chartLabels = $chartData->keys()->toArray();
        $chartData = $chartData->values()->toArray();

        return view('dashboard.dashboard', compact(
            'employees',
            'recentPayslips',
            'totalEmployees',
            'monthlyPayroll',
            'payslipsGenerated',
            'pendingTasks',
            'employeeGrowth',
            'payrollGrowth',
            'chartLabels',
            'chartData',
            'currentPeriod'
        ));
    }
}