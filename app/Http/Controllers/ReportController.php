<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\ComplianceTask;
use App\Models\LeaveRequest;
use App\Models\Setting;
use App\Models\Deduction;
use App\Models\Allowance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayrollSummaryExport;
use App\Exports\TaxReportExport;
use App\Exports\NSSFReportExport;
use App\Exports\NHIFReportExport;
use App\Exports\WCFReportExport;
use App\Exports\SDLReportExport;
use App\Exports\YearEndSummaryExport;
use App\Exports\PayslipExport;
use Carbon\Carbon;

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'payslip', 'payroll_summary', 'tax_report', 'nssf_report',
        'nhif_report', 'wcf_report', 'sdl_report', 'year_end_summary',
        'attendance_report', 'leave_report', 'compliance_report'
    ];

    private const EXPORT_FORMATS = ['pdf', 'excel'];
    private const STORAGE_DISK = 'public';
    private const REPORTS_PATH = 'reports';

    // Add employee allowed report types
    private const EMPLOYEE_ALLOWED_REPORTS = ['payslip'];

    private function cleanupOldReports()
    {
        $files = Storage::disk(self::STORAGE_DISK)->files(self::REPORTS_PATH);
        $threshold = now()->subDays(7)->timestamp;

        foreach ($files as $file) {
            if (Storage::disk(self::STORAGE_DISK)->lastModified($file) < $threshold) {
                Storage::disk(self::STORAGE_DISK)->delete($file);
                Log::info("Deleted old report file: {$file}");
            }
        }
    }

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show reports dashboard
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr']);
        $isEmployee = strtolower($user->role) === 'employee';

        $search = trim($request->query('search', ''));

        if ($isAdminOrHR) {
            // Admin/HR can see all reports
            $query = Report::with('employee')->latest();
        } else {
            // Employees can only see their own payslip reports
            $employeeId = $user->employee->id ?? 0;
            $query = Report::with('employee')
                        ->where('employee_id', $employeeId)
                        ->whereIn('type', self::EMPLOYEE_ALLOWED_REPORTS)
                        ->where('status', 'completed')
                        ->latest();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('report_id', 'like', "%{$search}%")
                ->orWhere('type', 'like', "%{$search}%")
                ->orWhereHas('employee', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            });
        }

        $reports = $query->paginate(10)->appends(['search' => $search]);
        $employees = Employee::orderBy('name')->get();
        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        Log::info('Reports fetched: ' . $reports->total() . ', Employees fetched: ' . $employees->count());
        return view('dashboard.report', compact('reports', 'employees', 'settings', 'isAdminOrHR', 'isEmployee'));
    }

    /**
     * Generate Report - COMPLETELY REWRITTEN
     */
    public function generate(Request $request)
    {
        Log::info('Report generation request: ' . json_encode($request->all()));

        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr']);

        if (!$isAdminOrHR) {
            return redirect()->route('reports')->with('error', 'Unauthorized. Only admin and HR can generate reports.');
        }

        $isYearlyReport = $request->input('report_type') === 'year_end_summary';
        $periodFormat = $isYearlyReport ? 'Y' : 'Y-m';

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:' . implode(',', self::REPORT_TYPES),
            'report_period' => "required|date_format:{$periodFormat}",
            'employee_id' => 'nullable|exists:employees,id',
            'export_format' => 'required|in:' . implode(',', self::EXPORT_FORMATS),
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed: ' . json_encode($validator->errors()->all()));
            return redirect()->route('reports')->withErrors($validator)->withInput();
        }

        $reportType = $request->input('report_type');
        $reportPeriod = $request->input('report_period');
        $employeeId = $request->input('employee_id');
        $exportFormat = $request->input('export_format');

        // If employee_id is empty, we're generating for all employees
        $forAllEmployees = empty($employeeId);

        try {
            $this->cleanupOldReports();

            if ($forAllEmployees) {
                return $this->generateBatchReport($reportType, $reportPeriod, $exportFormat, $user);
            } else {
                return $this->generateIndividualReport($reportType, $reportPeriod, $employeeId, $exportFormat, $user);
            }

        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            return redirect()->route('reports')->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }

    /**
     * Generate Batch Report for All Employees
     */
    private function generateBatchReport($reportType, $reportPeriod, $exportFormat, $user)
    {
        $batchNumber = $this->getNextBatchNumber($reportType, $reportPeriod);

        $report = Report::create([
            'report_id' => 'BATCH-' . $batchNumber . '-' . strtoupper(Str::random(6)),
            'type' => $reportType,
            'period' => $reportPeriod,
            'employee_id' => null,
            'batch_number' => $batchNumber,
            'export_format' => $exportFormat,
            'generated_by' => $user->id,
            'status' => 'pending',
        ]);

        $employees = Employee::with(['payslips' => function($query) use ($reportType, $reportPeriod) {
            if ($reportType === 'year_end_summary') {
                $query->where('period', 'like', "{$reportPeriod}%");
            } else {
                $query->where('period', $reportPeriod);
            }
        }])->get();

        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        // Get report data based on type
        $reportData = $this->getBatchReportData($reportType, $employees, $reportPeriod, $batchNumber, $settings);

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if ($exportFormat === 'pdf') {
            $view = $this->getBatchReportView($reportType);
            $pdf = Pdf::loadView($view, $reportData);
            Storage::disk(self::STORAGE_DISK)->put($filePath, $pdf->output());
        } elseif ($exportFormat === 'excel') {
            $exportClass = $this->getBatchExportClass($reportType);
            Excel::store(new $exportClass($reportData), $filePath, self::STORAGE_DISK);
        }

        $report->update(['status' => 'completed']);
        Log::info("Batch report generated: {$report->report_id}, File: {$filePath}");

        return redirect()->route('reports')->with('success', "Batch report #{$batchNumber} for all employees generated successfully!");
    }

    /**
     * Generate Individual Report for Specific Employee
     */
    private function generateIndividualReport($reportType, $reportPeriod, $employeeId, $exportFormat, $user)
    {
        $employee = Employee::findOrFail($employeeId);

        $report = Report::create([
            'report_id' => 'EMP-' . strtoupper(Str::random(8)),
            'type' => $reportType,
            'period' => $reportPeriod,
            'employee_id' => $employee->id,
            'export_format' => $exportFormat,
            'generated_by' => $user->id,
            'status' => 'pending',
        ]);

        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        // Get individual report data
        $reportData = $this->getIndividualReportData($reportType, $employee, $reportPeriod, $settings);

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if ($exportFormat === 'pdf') {
            $view = $this->getIndividualReportView($reportType);
            $pdf = Pdf::loadView($view, $reportData);
            Storage::disk(self::STORAGE_DISK)->put($filePath, $pdf->output());
        } elseif ($exportFormat === 'excel') {
            $exportClass = $this->getIndividualExportClass($reportType);
            Excel::store(new $exportClass($reportData), $filePath, self::STORAGE_DISK);
        }

        $report->update(['status' => 'completed']);
        Log::info("Individual report generated: {$report->report_id}, File: {$filePath}");

        return redirect()->route('reports')->with('success', "Report for {$employee->name} generated successfully!");
    }

    /**
     * Get Batch Report Data Based on Type
     */
    private function getBatchReportData($reportType, $employees, $period, $batchNumber, $settings)
    {
        $baseData = [
            'settings' => $settings,
            'period' => $period,
            'employees' => $employees,
            'batch_number' => $batchNumber,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
        ];

        switch ($reportType) {
            case 'payslip':
                return array_merge($baseData, $this->getPayslipBatchData($employees, $period));

            case 'payroll_summary':
                return array_merge($baseData, $this->getPayrollSummaryData($employees, $period));

            case 'tax_report':
                return array_merge($baseData, $this->getTaxReportData($employees, $period));

            case 'nssf_report':
                return array_merge($baseData, $this->getNSSFReportData($employees, $period));

            case 'nhif_report':
                return array_merge($baseData, $this->getNHIFReportData($employees, $period));

            case 'wcf_report':
                return array_merge($baseData, $this->getWCFReportData($employees, $period));

            case 'sdl_report':
                return array_merge($baseData, $this->getSDLReportData($employees, $period));

            case 'year_end_summary':
                return array_merge($baseData, $this->getYearEndSummaryData($employees, $period));

            case 'attendance_report':
                return array_merge($baseData, $this->getAttendanceReportData($employees, $period));

            case 'leave_report':
                return array_merge($baseData, $this->getLeaveReportData($employees, $period));

            case 'compliance_report':
                return array_merge($baseData, $this->getComplianceReportData($period));

            default:
                return $baseData;
        }
    }

    /**
     * Get Individual Report Data Based on Type
     */
    private function getIndividualReportData($reportType, $employee, $period, $settings)
    {
        $baseData = [
            'settings' => $settings,
            'period' => $period,
            'employee' => $employee,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
        ];

        switch ($reportType) {
            case 'payslip':
                $payslip = Payslip::where('employee_id', $employee->id)
                            ->where('period', $period)
                            ->first();
                return array_merge($baseData, ['payslip' => $payslip]);

            case 'payroll_summary':
                $payslips = Payslip::where('employee_id', $employee->id)
                            ->where('period', $period)
                            ->get();
                return array_merge($baseData, ['payslips' => $payslips]);

            case 'tax_report':
                $payslips = Payslip::where('employee_id', $employee->id)
                            ->where('period', $period)
                            ->get();
                return array_merge($baseData, ['payslips' => $payslips]);

            case 'attendance_report':
                $attendances = Attendance::where('employee_id', $employee->id)
                                ->where('date', 'like', "{$period}%")
                                ->get();
                return array_merge($baseData, ['attendances' => $attendances]);

            default:
                return $baseData;
        }
    }

    // ========== BATCH REPORT DATA METHODS ==========

    private function getPayslipBatchData($employees, $period)
    {
        $summary = $this->calculatePayrollSummary($employees, $period);
        return $summary;
    }

    private function getPayrollSummaryData($employees, $period)
    {
        return $this->calculatePayrollSummary($employees, $period);
    }

    private function getTaxReportData($employees, $period)
    {
        $taxData = [];
        $totalTax = 0;

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);
            $employeeTax = $payslips->sum(function($payslip) {
                // Calculate PAYE tax based on salary
                return $this->calculatePAYE($payslip->gross_salary ?? ($payslip->base_salary + $payslip->allowances));
            });

            $taxData[] = [
                'employee' => $employee,
                'tax_amount' => $employeeTax,
                'payslip_count' => $payslips->count()
            ];

            $totalTax += $employeeTax;
        }

        return [
            'taxData' => $taxData,
            'totalTax' => $totalTax,
            'employeeCount' => count($taxData)
        ];
    }

    private function getNSSFReportData($employees, $period)
    {
        $nssfData = [];
        $totalNSSF = 0;

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);
            $employeeNSSF = $payslips->sum(function($payslip) {
                return $this->calculateNSSF($payslip->gross_salary ?? ($payslip->base_salary + $payslip->allowances));
            });

            $nssfData[] = [
                'employee' => $employee,
                'nssf_amount' => $employeeNSSF,
                'nssf_number' => $employee->nssf_number
            ];

            $totalNSSF += $employeeNSSF;
        }

        return [
            'nssfData' => $nssfData,
            'totalNSSF' => $totalNSSF,
            'employeeCount' => count($nssfData)
        ];
    }

    private function getNHIFReportData($employees, $period)
    {
        $nhifData = [];
        $totalNHIF = 0;

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);
            $employeeNHIF = $payslips->sum(function($payslip) {
                return $this->calculateNHIF($payslip->gross_salary ?? ($payslip->base_salary + $payslip->allowances));
            });

            $nhifData[] = [
                'employee' => $employee,
                'nhif_amount' => $employeeNHIF,
                'nhif_number' => $employee->nhif_number
            ];

            $totalNHIF += $employeeNHIF;
        }

        return [
            'nhifData' => $nhifData,
            'totalNHIF' => $totalNHIF,
            'employeeCount' => count($nhifData)
        ];
    }

    private function getWCFReportData($employees, $period)
    {
        $wcfData = [];
        $totalWCF = 0;
        $wcfRate = 0.005; // 0.5%

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);
            $employeeWCF = $payslips->sum(function($payslip) use ($wcfRate) {
                $grossSalary = $payslip->gross_salary ?? ($payslip->base_salary + $payslip->allowances);
                return $grossSalary * $wcfRate;
            });

            $wcfData[] = [
                'employee' => $employee,
                'wcf_amount' => $employeeWCF
            ];

            $totalWCF += $employeeWCF;
        }

        return [
            'wcfData' => $wcfData,
            'totalWCF' => $totalWCF,
            'wcfRate' => $wcfRate,
            'employeeCount' => count($wcfData)
        ];
    }

    private function getSDLReportData($employees, $period)
    {
        $sdlData = [];
        $totalSDL = 0;
        $sdlRate = 0.035; // 3.5%

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);
            $employeeSDL = $payslips->sum(function($payslip) use ($sdlRate) {
                $grossSalary = $payslip->gross_salary ?? ($payslip->base_salary + $payslip->allowances);
                return $grossSalary * $sdlRate;
            });

            $sdlData[] = [
                'employee' => $employee,
                'sdl_amount' => $employeeSDL
            ];

            $totalSDL += $employeeSDL;
        }

        return [
            'sdlData' => $sdlData,
            'totalSDL' => $totalSDL,
            'sdlRate' => $sdlRate,
            'employeeCount' => count($sdlData)
        ];
    }

private function getYearEndSummaryData($employees, $period)
{
    $yearlyData = [];
    $annualTotals = $this->getDefaultAnnualTotals();

    foreach ($employees as $employee) {
        // Fix: Use proper yearly filtering
        $payslips = $employee->payslips->filter(function($payslip) use ($period) {
            // Check if period starts with the year (e.g., "2025-01", "2025-02", etc.)
            return substr($payslip->period, 0, 4) === $period;
        });
        
        // Debug logging
        Log::info("Employee {$employee->name}: {$payslips->count()} payslips for year {$period}");

        if ($payslips->count() > 0) {
            $grossSalary = $payslips->sum('gross_salary') ?? 0;
            $netSalary = $payslips->sum('net_salary') ?? 0;
            
            // Use actual calculations based on available data
            $taxAmount = $payslips->sum(function($payslip) {
                return $this->calculatePAYE($payslip->gross_salary ?? 0);
            });
            
            $nssfAmount = $payslips->sum(function($payslip) {
                return $this->calculateNSSF($payslip->gross_salary ?? 0);
            });
            
            $nhifAmount = $payslips->sum(function($payslip) {
                return $this->calculateNHIF($payslip->gross_salary ?? 0);
            });

            $employeeYearly = [
                'employee' => $employee,
                'gross_salary' => $grossSalary,
                'net_salary' => $netSalary,
                'tax_amount' => $taxAmount,
                'nssf_amount' => $nssfAmount,
                'nhif_amount' => $nhifAmount,
                'payslip_count' => $payslips->count()
            ];

            $yearlyData[] = $employeeYearly;
            
            // Update annual totals
            $annualTotals['gross'] += $grossSalary;
            $annualTotals['net'] += $netSalary;
            $annualTotals['tax'] += $taxAmount;
            $annualTotals['nssf'] += $nssfAmount;
            $annualTotals['nhif'] += $nhifAmount;
        } else {
            // Log employees without payslips for debugging
            Log::warning("No payslips found for employee {$employee->name} in year {$period}");
        }
    }

    // Log final results for debugging
    Log::info("Year-end summary generated: " . count($yearlyData) . " employees with data");

    return [
        'yearlyData' => $yearlyData,
        'annualTotals' => $annualTotals,
        'employeeCount' => count($yearlyData)
    ];
}

    private function getAttendanceReportData($employees, $period)
    {
        $attendanceData = [];

        foreach ($employees as $employee) {
            $attendances = Attendance::where('employee_id', $employee->id)
                            ->where('date', 'like', "{$period}%")
                            ->get();

            $presentDays = $attendances->where('status', 'Present')->count();
            $absentDays = $attendances->where('status', 'Absent')->count();
            $leaveDays = $attendances->where('status', 'Leave')->count();

            $attendanceData[] = [
                'employee' => $employee,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'leave_days' => $leaveDays,
                'total_days' => $attendances->count(),
                'attendance_rate' => $attendances->count() > 0 ? ($presentDays / $attendances->count()) * 100 : 0
            ];
        }

        return [
            'attendanceData' => $attendanceData,
            'totalEmployees' => count($attendanceData)
        ];
    }

    private function getLeaveReportData($employees, $period)
    {
        $leaveData = LeaveRequest::with('employee')
                        ->where('start_date', 'like', "{$period}%")
                        ->get()
                        ->groupBy('leave_type');

        $summary = [];
        foreach ($leaveData as $type => $requests) {
            $summary[$type] = [
                'count' => $requests->count(),
                'total_days' => $requests->sum('days'),
                'approved' => $requests->where('status', 'Approved')->count(),
                'pending' => $requests->where('status', 'Pending')->count()
            ];
        }

        return [
            'leaveData' => $leaveData,
            'summary' => $summary,
            'totalRequests' => $leaveData->flatten()->count()
        ];
    }

    private function getComplianceReportData($period)
    {
        $complianceTasks = ComplianceTask::with('employee')
                            ->where('due_date', 'like', "{$period}%")
                            ->get()
                            ->groupBy('type');

        $summary = [];
        foreach ($complianceTasks as $type => $tasks) {
            $summary[$type] = [
                'total' => $tasks->count(),
                'completed' => $tasks->where('status', 'Completed')->count(),
                'pending' => $tasks->where('status', 'Pending')->count(),
                'overdue' => $tasks->where('status', 'Overdue')->count()
            ];
        }

        return [
            'complianceTasks' => $complianceTasks,
            'summary' => $summary,
            'totalTasks' => $complianceTasks->flatten()->count()
        ];
    }

    // ========== CALCULATION METHODS ==========

    private function calculatePayrollSummary($employees, $period)
    {
        $employeesWithPayslips = 0;
        $totalGross = 0;
        $totalNet = 0;
        $totalDeductions = 0;

        foreach ($employees as $employee) {
            $payslips = $employee->payslips->where('period', $period);

            if ($payslips->count() > 0) {
                $employeesWithPayslips++;
                $totalGross += $payslips->sum('gross_salary');
                $totalNet += $payslips->sum('net_salary');
                $totalDeductions += $payslips->sum('deductions');
            }
        }

        $averageNet = $employeesWithPayslips > 0 ? $totalNet / $employeesWithPayslips : 0;
        $averageGross = $employeesWithPayslips > 0 ? $totalGross / $employeesWithPayslips : 0;

        return [
            'employeesWithPayslips' => $employeesWithPayslips,
            'totalGross' => $totalGross,
            'totalNet' => $totalNet,
            'totalDeductions' => $totalDeductions,
            'averageNet' => $averageNet,
            'averageGross' => $averageGross,
            'employeeCount' => $employees->count(),
            'coveragePercentage' => $employees->count() > 0 ? ($employeesWithPayslips / $employees->count()) * 100 : 0,
        ];
    }

    private function calculatePAYE($salary)
    {
        // Tanzanian PAYE tax calculation
        $taxableIncome = $salary - $this->calculateNSSF($salary);

        if ($taxableIncome <= 270000) return 0;
        if ($taxableIncome <= 520000) return ($taxableIncome - 270000) * 0.08;
        if ($taxableIncome <= 760000) return 20000 + ($taxableIncome - 520000) * 0.20;
        if ($taxableIncome <= 1000000) return 68000 + ($taxableIncome - 760000) * 0.25;

        return 118000 + ($taxableIncome - 1000000) * 0.30;
    }

    private function calculateNSSF($salary)
    {
        // NSSF calculation - 10% of basic salary up to limit
        $nssfLimit = 200000;
        $nssfRate = 0.10;

        return min($salary * $nssfRate, $nssfLimit * $nssfRate);
    }

    private function calculateNHIF($salary)
    {
        // NHIF tiered rates
        if ($salary <= 5000) return 150;
        if ($salary <= 6000) return 300;
        if ($salary <= 8000) return 400;
        if ($salary <= 10000) return 500;
        if ($salary <= 12000) return 600;
        if ($salary <= 15000) return 750;
        if ($salary <= 20000) return 850;
        if ($salary <= 25000) return 900;
        if ($salary <= 30000) return 950;
        if ($salary <= 35000) return 1000;
        if ($salary <= 40000) return 1100;
        if ($salary <= 45000) return 1200;
        if ($salary <= 50000) return 1300;
        if ($salary <= 60000) return 1400;
        if ($salary <= 70000) return 1500;
        if ($salary <= 80000) return 1600;
        if ($salary <= 90000) return 1700;
        if ($salary <= 100000) return 1800;

        return 2000;
    }

    // ========== VIEW AND EXPORT METHODS ==========

    private function getBatchReportView($reportType)
    {
        $viewMap = [
            'payslip' => 'reports.batch_payslip',
            'payroll_summary' => 'reports.batch_payroll_summary',
            'tax_report' => 'reports.batch_tax_report',
            'nssf_report' => 'reports.batch_nssf_report',
            'nhif_report' => 'reports.batch_nhif_report',
            'wcf_report' => 'reports.batch_wcf_report',
            'sdl_report' => 'reports.batch_sdl_report',
            'year_end_summary' => 'reports.batch_year_end_summary',
            'attendance_report' => 'reports.batch_attendance_report',
            'leave_report' => 'reports.batch_leave_report',
            'compliance_report' => 'reports.batch_compliance_report',
        ];

        return $viewMap[$reportType] ?? 'reports.batch_default';
    }

    private function getIndividualReportView($reportType)
    {
        $viewMap = [
            'payslip' => 'reports.individual_payslip',
            'payroll_summary' => 'reports.individual_payroll_summary',
            'tax_report' => 'reports.individual_tax_report',
            'attendance_report' => 'reports.individual_attendance_report',
        ];

        return $viewMap[$reportType] ?? 'reports.individual_default';
    }

    private function getBatchExportClass($reportType)
    {
        $exportMap = [
            'payslip' => PayslipExport::class,
            'payroll_summary' => PayrollSummaryExport::class,
            'tax_report' => TaxReportExport::class,
            'nssf_report' => NSSFReportExport::class,
            'nhif_report' => NHIFReportExport::class,
            'wcf_report' => WCFReportExport::class,
            'sdl_report' => SDLReportExport::class,
            'year_end_summary' => YearEndSummaryExport::class,
        ];

        return $exportMap[$reportType] ?? PayslipExport::class;
    }

    private function getIndividualExportClass($reportType)
    {
        // Similar to batch but for individual exports
        return $this->getBatchExportClass($reportType);
    }

    /**
     * Get the next batch number for a report type and period
     */
    private function getNextBatchNumber($reportType, $period)
    {
        try {
            $lastBatch = Report::where('type', $reportType)
                ->where('period', $period)
                ->whereNull('employee_id')
                ->orderBy('batch_number', 'desc')
                ->first();

            return $lastBatch ? $lastBatch->batch_number + 1 : 1;
        } catch (\Exception $e) {
            return 1;
        }
    }

    /**
     * Download report
     */
    public function download($id)
    {
        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr']);
        $isEmployee = strtolower($user->role) === 'employee';

        if ($isAdminOrHR) {
            $report = Report::where('status', 'completed')->findOrFail($id);
        } elseif ($isEmployee) {
            $employeeId = $user->employee->id ?? 0;
            $report = Report::where('status', 'completed')
                        ->where('employee_id', $employeeId)
                        ->whereIn('type', self::EMPLOYEE_ALLOWED_REPORTS)
                        ->findOrFail($id);
        } else {
            return redirect()->route('reports')->with('error', 'Unauthorized access.');
        }

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if (!Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
            Log::error("Report file not found: {$filePath}");
            return redirect()->route('reports')->with('error', 'Report file not found.');
        }

        return Storage::disk(self::STORAGE_DISK)->download($filePath, $filename);
    }

    /**
     * Delete report
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr']);

        if (!$isAdminOrHR) {
            return redirect()->route('reports')->with('error', 'Unauthorized. Only admin and HR can delete reports.');
        }

        $report = Report::where('status', 'completed')->findOrFail($id);

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if (Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
            Storage::disk(self::STORAGE_DISK)->delete($filePath);
        }

        $report->delete();

        return redirect()->route('reports')->with('success', 'Report deleted successfully!');
    }
    private function getNHIFTier($salary)
{
    if ($salary <= 5000) return 'Up to 5,000';
    if ($salary <= 6000) return '5,001 - 6,000';
    if ($salary <= 8000) return '6,001 - 8,000';
    if ($salary <= 10000) return '8,001 - 10,000';
    if ($salary <= 12000) return '10,001 - 12,000';
    if ($salary <= 15000) return '12,001 - 15,000';
    if ($salary <= 20000) return '15,001 - 20,000';
    if ($salary <= 25000) return '20,001 - 25,000';
    if ($salary <= 30000) return '25,001 - 30,000';
    if ($salary <= 35000) return '30,001 - 35,000';
    if ($salary <= 40000) return '35,001 - 40,000';
    if ($salary <= 45000) return '40,001 - 45,000';
    if ($salary <= 50000) return '45,001 - 50,000';
    if ($salary <= 60000) return '50,001 - 60,000';
    if ($salary <= 70000) return '60,001 - 70,000';
    if ($salary <= 80000) return '70,001 - 80,000';
    if ($salary <= 90000) return '80,001 - 90,000';
    if ($salary <= 100000) return '90,001 - 100,000';
    return 'Above 100,000';
}

/**
 * Safe division method to prevent division by zero
 */
private function safeDivide($numerator, $denominator, $default = 0)
{
    return $denominator != 0 ? $numerator / $denominator : $default;
}

/**
 * Safe percentage calculation
 */
private function safePercentage($part, $whole, $default = 0)
{
    return $whole != 0 ? ($part / $whole) * 100 : $default;
}

/**
 * Get default values for annual totals to prevent undefined array key errors
 */
private function getDefaultAnnualTotals()
{
    return [
        'gross' => 0,
        'net' => 0,
        'tax' => 0,
        'nssf' => 0,
        'nhif' => 0
    ];
}
}
