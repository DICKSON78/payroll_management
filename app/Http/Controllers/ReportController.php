<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Payroll;
use Illuminate\Routing\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'payslip', 'payroll_summary', 'tax_report', 'nssf_report',
        'nhif_report', 'wcf_report', 'sdl_report', 'year_end_summary',
        'attendance_report', 'leave_report', 'compliance_report'
    ];

    // Reports for Admin/HR only (siyo kwa wafanyakazi wengine)
    private const ADMIN_ONLY_REPORTS = [
        'payroll_summary', 'tax_report', 'nssf_report', 'nhif_report',
        'wcf_report', 'sdl_report', 'year_end_summary', 'attendance_report',
        'leave_report', 'compliance_report'
    ];

    private const EXPORT_FORMATS = ['pdf', 'csv'];
    private const STORAGE_DISK = 'public';
    private const REPORTS_PATH = 'reports';

    // Kanuni za Tanzania za Ushuru na Mchango
    private const TAX_BRACKETS = [
        ['limit' => 270000, 'rate' => 0.00],   // Tax-free threshold
        ['limit' => 520000, 'rate' => 0.08],   // 8% for next 250,000
        ['limit' => 760000, 'rate' => 0.20],   // 20% for next 240,000
        ['limit' => 1000000, 'rate' => 0.25],  // 25% for next 240,000
        ['limit' => PHP_FLOAT_MAX, 'rate' => 0.30] // 30% for the rest
    ];

    private const NSSF_RATE_EMPLOYEE = 0.10; // 10% for employee
    private const NSSF_RATE_EMPLOYER = 0.10; // 10% for employer
    private const NSSF_LIMIT = 2000000; // Maximum insurable earnings

    // NHIF rates according to Tanzanian law (2024)
    private const NHIF_RATES = [
        5000 => 150,
        6000 => 300,
        8000 => 400,
        10000 => 500,
        12000 => 600,
        15000 => 750,
        20000 => 850,
        25000 => 900,
        30000 => 950,
        35000 => 1000,
        40000 => 1100,
        45000 => 1200,
        50000 => 1300,
        60000 => 1400,
        70000 => 1500,
        80000 => 1600,
        90000 => 1700,
        100000 => 1800,
        110000 => 1900,
        120000 => 2000,
        130000 => 2100,
        140000 => 2200,
        150000 => 2300,
        PHP_INT_MAX => 2400
    ];

    // WCF (Workers Compensation Fund) - Employer pays 0.5%
    private const WCF_RATE = 0.005;

    // SDL (Skills Development Levy) - Employer pays 3.5%
    private const SDL_RATE = 0.035;

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

        // Employees wanaweza kuona payslips zao tu
        if ($isEmployee) {
            $employeeId = $user->employee_id ?? $user->employee->employee_id ?? null;

            $query = Report::with('employee')
                ->where('type', 'payslip')
                ->where('employee_id', $employeeId)
                ->where('status', 'completed')
                ->latest();

            $reports = $query->paginate(10);
            $employees = collect(); // Employees hawana need ya kuchagua wengine
        } else {
            // Admin/HR wanaweza kuona reports zote
            $search = $request->query('search', '');
            $query = Report::with('employee')->latest();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('report_id', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%")
                      ->orWhereHas('employee', function ($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $reports = $query->paginate(10);
            $employees = Employee::where('status', 'active')->orderBy('name')->get();
        }

        $settings = Setting::first() ?? new Setting();

        return view('dashboard.report', compact('reports', 'employees', 'settings', 'isAdminOrHR', 'isEmployee'));
    }

    /**
     * Generate Report
     */
    public function generate(Request $request)
    {
        $user = Auth::user();
        $isAdminOrHR = in_array(strtolower($user->role), ['admin', 'hr']);

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:' . implode(',', self::REPORT_TYPES),
            'report_period' => 'required',
            'employee_id' => 'nullable|exists:employees,employee_id',
            'export_format' => 'required|in:' . implode(',', self::EXPORT_FORMATS),
        ]);

        if ($validator->fails()) {
            return redirect()->route('reports')
                ->withErrors($validator)
                ->withInput();
        }

        $reportType = $request->report_type;
        $reportPeriod = $request->report_period;
        $employeeId = $request->employee_id;
        $exportFormat = $request->export_format;

        // Validate period based on report type
        if ($reportType === 'year_end_summary') {
            if (!preg_match('/^\d{4}$/', $reportPeriod)) {
                return redirect()->route('reports')
                    ->with('error', 'For year end summary, period must be a valid year (e.g., 2025)')
                    ->withInput();
            }
        } else {
            if (!preg_match('/^\d{4}-\d{2}$/', $reportPeriod)) {
                return redirect()->route('reports')
                    ->with('error', 'Period must be in YYYY-MM format (e.g., 2025-10)')
                    ->withInput();
            }
        }

        // Ukaguzi wa ruhusa
        if (in_array($reportType, self::ADMIN_ONLY_REPORTS) && !$isAdminOrHR) {
            return redirect()->route('reports')
                ->with('error', 'Unauthorized. This report type is for administrators only.')
                ->withInput();
        }

        // Employees wanaweza kugenerate payslip zao tu
        if (!$isAdminOrHR && $reportType !== 'payslip') {
            return redirect()->route('reports')
                ->with('error', 'Unauthorized. You can only generate payslip reports.')
                ->withInput();
        }

        // Employee anajaribu kugenerate payslip ya mwingine
        if (!$isAdminOrHR && !empty($employeeId)) {
            $employee = Employee::where('employee_id', $employeeId)->first();
            if ($employee && $employee->employee_id !== ($user->employee_id ?? $user->employee->employee_id)) {
                return redirect()->route('reports')
                    ->with('error', 'Unauthorized. You can only generate your own payslip.')
                    ->withInput();
            }
        }

        try {
            $this->cleanupOldReports();

            if (empty($employeeId)) {
                return $this->generateBatchReport($reportType, $reportPeriod, $exportFormat, $user);
            } else {
                return $this->generateIndividualReport($reportType, $reportPeriod, $employeeId, $exportFormat, $user);
            }

        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            return redirect()->route('reports')
                ->with('error', 'Failed to generate report: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Generate Batch Report for All Employees
     */
    private function generateBatchReport($reportType, $reportPeriod, $exportFormat, $user)
    {
        try {
            // Payslip batch - generate for all employees
            if ($reportType === 'payslip') {
                return $this->generateBatchPayslip($reportPeriod, $exportFormat, $user);
            }

            // Other reports - for Admin/HR only
            $systemEmployee = Employee::where('role', 'admin')->first();
            if (!$systemEmployee) {
                $systemEmployee = Employee::first();
            }

            $batchNumber = $this->getNextBatchNumber($reportType, $reportPeriod);

            $report = Report::create([
                'report_id' => 'BATCH-' . $batchNumber . '-' . strtoupper(Str::random(6)),
                'type' => $reportType,
                'period' => $reportPeriod,
                'employee_id' => $systemEmployee->employee_id,
                'batch_number' => $batchNumber,
                'export_format' => $exportFormat,
                'generated_by' => $user->employee_id,
                'status' => 'pending',
            ]);

            // Get report data
            $reportData = $this->getBatchReportData($reportType, $reportPeriod, $batchNumber);

            // Generate file
            $filename = "{$report->report_id}_{$report->type}_{$report->period}";
            $filePath = self::REPORTS_PATH . '/' . $filename;

            if ($exportFormat === 'pdf') {
                $view = $this->getBatchReportView($reportType);

                $pdf = Pdf::loadView($view, $reportData)
                    ->setPaper('a4', 'landscape')
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => false,
                        'isPhpEnabled' => false,
                        'dpi' => 72,
                        'defaultFont' => 'helvetica',
                        'chroot' => base_path(),
                        'fontHeightRatio' => 0.9,
                        'isFontSubsettingEnabled' => true,
                    ]);

                Storage::disk(self::STORAGE_DISK)->put($filePath . '.pdf', $pdf->output());
            } else {
                $csvData = $this->generateCsvData($reportData, $reportType);
                Storage::disk(self::STORAGE_DISK)->put($filePath . '.csv', $csvData);
            }

            $report->update(['status' => 'completed']);

            return redirect()->route('reports')
                ->with('success', "Batch {$reportType} report for {$reportPeriod} generated successfully!");

        } catch (\Exception $e) {
            Log::error('Batch report generation failed: ' . $e->getMessage());

            // Fallback to CSV on any error
            if (isset($report) && isset($reportData)) {
                try {
                    $csvData = $this->generateCsvData($reportData, $reportType);
                    Storage::disk(self::STORAGE_DISK)->put($filePath . '.csv', $csvData);
                    $report->update(['status' => 'completed', 'export_format' => 'csv']);

                    return redirect()->route('reports')
                        ->with('warning', "Report generated as CSV due to PDF issues: " . $e->getMessage());
                } catch (\Exception $csvError) {
                    Log::error('CSV fallback also failed: ' . $csvError->getMessage());
                }
            }

            throw $e;
        }
    }

    /**
     * Generate Batch Payslip for All Employees
     */
    private function generateBatchPayslip($reportPeriod, $exportFormat, $user)
    {
        try {
            $employees = Employee::where('status', 'active')->get();
            $generatedCount = 0;

            foreach ($employees as $employee) {
                // Check if payslip already exists for this period
                $existingPayslip = Report::where('type', 'payslip')
                    ->where('employee_id', $employee->employee_id)
                    ->where('period', $reportPeriod)
                    ->first();

                if (!$existingPayslip) {
                    $report = Report::create([
                        'report_id' => 'PAYSLIP-' . strtoupper(Str::random(8)),
                        'type' => 'payslip',
                        'period' => $reportPeriod,
                        'employee_id' => $employee->employee_id,
                        'export_format' => $exportFormat,
                        'generated_by' => $user->employee_id,
                        'status' => 'pending',
                    ]);

                    // Generate individual payslip
                    $reportData = $this->getIndividualReportData('payslip', $employee, $reportPeriod);
                    $filename = "{$report->report_id}_{$report->type}_{$report->period}";
                    $filePath = self::REPORTS_PATH . '/' . $filename;

                    if ($exportFormat === 'pdf') {
                        $view = $this->getIndividualReportView('payslip');
                        $pdf = Pdf::loadView($view, $reportData)
                            ->setOptions([
                                'isHtml5ParserEnabled' => true,
                                'isRemoteEnabled' => true,
                                'isPhpEnabled' => false,
                                'dpi' => 96,
                                'defaultFont' => 'dejavu sans',
                            ]);
                        Storage::disk(self::STORAGE_DISK)->put($filePath . '.pdf', $pdf->output());
                    } else {
                        $csvData = $this->generateCsvData($reportData, 'payslip');
                        Storage::disk(self::STORAGE_DISK)->put($filePath . '.csv', $csvData);
                    }

                    $report->update(['status' => 'completed']);
                    $generatedCount++;
                }
            }

            return redirect()->route('reports')
                ->with('success', "Batch payslip generation completed! Generated {$generatedCount} payslips for {$reportPeriod}.");

        } catch (\Exception $e) {
            Log::error('Batch payslip generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate Individual Report for Specific Employee
     */
    private function generateIndividualReport($reportType, $reportPeriod, $employeeId, $exportFormat, $user)
    {
        try {
            $employee = Employee::where('employee_id', $employeeId)->firstOrFail();

            // For payslip, check if already exists
            if ($reportType === 'payslip') {
                $existingPayslip = Report::where('type', 'payslip')
                    ->where('employee_id', $employee->employee_id)
                    ->where('period', $reportPeriod)
                    ->first();

                if ($existingPayslip) {
                    return redirect()->route('reports')
                        ->with('warning', "Payslip for {$employee->name} ({$reportPeriod}) already exists.");
                }
            }

            $report = Report::create([
                'report_id' => strtoupper($reportType) . '-' . strtoupper(Str::random(8)),
                'type' => $reportType,
                'period' => $reportPeriod,
                'employee_id' => $employee->employee_id,
                'export_format' => $exportFormat,
                'generated_by' => $user->employee_id,
                'status' => 'pending',
            ]);

            // Get report data
            $reportData = $this->getIndividualReportData($reportType, $employee, $reportPeriod);

            // Generate file
            $filename = "{$report->report_id}_{$report->type}_{$report->period}";
            $filePath = self::REPORTS_PATH . '/' . $filename;

            if ($exportFormat === 'pdf') {
                try {
                    $view = $this->getIndividualReportView($reportType);

                    $pdf = Pdf::loadView($view, $reportData)
                        ->setOptions([
                            'isHtml5ParserEnabled' => true,
                            'isRemoteEnabled' => true,
                            'isPhpEnabled' => false,
                            'dpi' => 96,
                            'defaultFont' => 'dejavu sans',
                            'chroot' => base_path(),
                        ]);

                    Storage::disk(self::STORAGE_DISK)->put($filePath . '.pdf', $pdf->output());

                } catch (\Exception $e) {
                    \Log::error('PDF Generation Error: ' . $e->getMessage());
                    // Fallback kwa CSV kama PDF inakataa
                    $csvData = $this->generateCsvData($reportData, $reportType);
                    Storage::disk(self::STORAGE_DISK)->put($filePath . '.csv', $csvData);
                }
            } else {
                $csvData = $this->generateCsvData($reportData, $reportType);
                Storage::disk(self::STORAGE_DISK)->put($filePath . '.csv', $csvData);
            }

            $report->update(['status' => 'completed']);

            $message = $reportType === 'payslip'
                ? "Payslip for {$employee->name} ({$reportPeriod}) generated successfully!"
                : "{$reportType} report for {$employee->name} generated successfully!";

            return redirect()->route('reports')->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Individual report generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get Batch Report Data (For Admin/HR Reports)
     */
    private function getBatchReportData($reportType, $period, $batchNumber)
    {
        $settings = Setting::first() ?? new Setting();

        $baseData = [
            'settings' => $settings,
            'batch_number' => $batchNumber,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
        ];

        switch ($reportType) {
            case 'payroll_summary':
                return array_merge($baseData, [
                    'period' => $period,
                    'period_display' => Carbon::parse($period . '-01')->format('F Y')
                ], $this->getPayrollSummaryData($period));

            case 'tax_report':
                return array_merge($baseData, [
                    'period' => $period,
                    'period_display' => Carbon::parse($period . '-01')->format('F Y')
                ], $this->getTaxReportData($period));

            case 'nssf_report':
                return array_merge($baseData, [
                    'period' => $period,
                    'period_display' => Carbon::parse($period . '-01')->format('F Y')
                ], $this->getNSSFReportData($period));

            case 'nhif_report':
                return array_merge($baseData, [
                    'period' => $period,
                    'period_display' => Carbon::parse($period . '-01')->format('F Y')
                ], $this->getNHIFReportData($period));

            case 'year_end_summary':
                $year = $period;
                return array_merge($baseData, [
                    'period' => $year,
                    'period_display' => "Mwaka wa {$year}"
                ], $this->getYearEndSummaryData($year));

            default:
                return array_merge($baseData, [
                    'period' => $period,
                    'period_display' => $period
                ]);
        }
    }

    /**
     * Get Individual Report Data
     */
    private function getIndividualReportData($reportType, $employee, $period)
    {
        $settings = Setting::first() ?? new Setting();

        $baseData = [
            'settings' => $settings,
            'period' => $period,
            'employee' => $employee,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
            'period_display' => Carbon::parse($period . '-01')->format('F Y'),
        ];

        switch ($reportType) {
            case 'payslip':
                $payslip = Payslip::where('employee_id', $employee->employee_id)
                            ->where('period', $period)
                            ->first();
                $payroll = Payroll::where('employee_id', $employee->employee_id)
                            ->where('period', $period)
                            ->first();
                return array_merge($baseData, [
                    'payslip' => $payslip,
                    'payroll' => $payroll,
                    'deduction_breakdown' => $this->getDeductionBreakdown($payroll, $employee)
                ]);

            default:
                return $baseData;
        }
    }

    /**
     * Get Payroll Summary Data - IMEFUNGULIWA KWA JOIN DIRECT
     */
    private function getPayrollSummaryData($period)
    {
        // Tumia DB join kupata data moja kwa moja
        $payrollData = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->where('payrolls.period', $period)
            ->where('payrolls.status', 'Processed')
            ->select(
                'payrolls.*',
                'employees.position',
                'employees.account_number',
                'employees.name as employee_name'
            )
            ->get();

        $employeeData = [];
        $totals = [
            'total_basic_salary' => 0,
            'total_allowance' => 0,
            'total_gross_salary' => 0,
            'total_nssf' => 0,
            'total_payee' => 0,
            'total_bima' => 0,
            'total_bodi_mikopo' => 0,
            'total_tuico' => 0,
            'total_madeni_nafsia' => 0,
            'total_take_home' => 0,
            'employee_count' => $payrollData->count()
        ];

        foreach ($payrollData as $index => $payroll) {
            // Calculate statutory deductions
            $grossSalary = $payroll->total_amount;
            $nssf = $this->calculateNSSF($grossSalary);
            $nhif = $this->calculateNHIF($grossSalary);
            $payee = $this->calculatePAYE($grossSalary, $nssf);
            $otherDeductions = max(0, $payroll->deductions - ($nssf + $nhif + $payee));

            // SASA IMEWAKA - data halisi kutoka database
            $employeeData[] = [
                'na' => $index + 1,
                'name' => $payroll->employee_name,
                'position' => $payroll->position ?? 'N/A',
                'basic_salary' => $payroll->base_salary,
                'allowance' => $payroll->allowances ?? 0,
                'gross_salary' => $grossSalary,
                'nssf' => $nssf,
                'payee' => $payee,
                'bima' => $nhif,
                'bodi_mikopo' => 0,
                'tuico' => 0,
                'madeni_nafsia' => $otherDeductions,
                'take_home' => $payroll->net_salary,
                'account' => $payroll->account_number ?? 'N/A'
            ];

            // Update totals
            $totals['total_basic_salary'] += $payroll->base_salary;
            $totals['total_allowance'] += $payroll->allowances ?? 0;
            $totals['total_gross_salary'] += $grossSalary;
            $totals['total_nssf'] += $nssf;
            $totals['total_payee'] += $payee;
            $totals['total_bima'] += $nhif;
            $totals['total_bodi_mikopo'] += 0;
            $totals['total_tuico'] += 0;
            $totals['total_madeni_nafsia'] += $otherDeductions;
            $totals['total_take_home'] += $payroll->net_salary;
        }

        return [
            'employees' => $employeeData,
            'totals' => $totals
        ];
    }

    /**
     * Get Tax Report Data
     */
    private function getTaxReportData($period)
    {
        $payrollData = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->where('payrolls.period', $period)
            ->where('payrolls.status', 'Processed')
            ->select(
                'payrolls.*',
                'employees.position',
                'employees.account_number',
                'employees.nssf_number',
                'employees.tin_number',
                'employees.name as employee_name'
            )
            ->get();

        $taxData = [];
        $totalTax = 0;

        foreach ($payrollData as $payroll) {
            $grossSalary = $payroll->total_amount;
            $nssf = $this->calculateNSSF($grossSalary);
            $employeeTax = $this->calculatePAYE($grossSalary, $nssf);

            $taxData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'position' => $payroll->position ?? 'N/A',
                'nssf_number' => $payroll->nssf_number ?? 'N/A',
                'tin_number' => $payroll->tin_number ?? 'N/A',
                'gross_salary' => $grossSalary,
                'nssf_deduction' => $nssf,
                'taxable_income' => $grossSalary - $nssf,
                'tax_amount' => $employeeTax,
                'account_number' => $payroll->account_number ?? 'N/A'
            ];

            $totalTax += $employeeTax;
        }

        return [
            'taxData' => $taxData,
            'totalTax' => $totalTax,
            'employeeCount' => count($taxData)
        ];
    }

    /**
     * Get NSSF Report Data
     */
    private function getNSSFReportData($period)
    {
        $payrollData = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->where('payrolls.period', $period)
            ->where('payrolls.status', 'Processed')
            ->select(
                'payrolls.*',
                'employees.position',
                'employees.account_number',
                'employees.nssf_number',
                'employees.name as employee_name'
            )
            ->get();

        $nssfData = [];
        $totalEmployeeNSSF = 0;
        $totalEmployerNSSF = 0;

        foreach ($payrollData as $payroll) {
            $grossSalary = $payroll->total_amount;
            $employeeNSSF = $this->calculateNSSF($grossSalary);
            $employerNSSF = $this->calculateNSSFEmployer($grossSalary);

            $nssfData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'position' => $payroll->position ?? 'N/A',
                'nssf_number' => $payroll->nssf_number ?? 'N/A',
                'gross_salary' => $grossSalary,
                'employee_contribution' => $employeeNSSF,
                'employer_contribution' => $employerNSSF,
                'total_contribution' => $employeeNSSF + $employerNSSF,
                'account_number' => $payroll->account_number ?? 'N/A'
            ];

            $totalEmployeeNSSF += $employeeNSSF;
            $totalEmployerNSSF += $employerNSSF;
        }

        return [
            'nssfData' => $nssfData,
            'totalEmployeeNSSF' => $totalEmployeeNSSF,
            'totalEmployerNSSF' => $totalEmployerNSSF,
            'totalNSSF' => $totalEmployeeNSSF + $totalEmployerNSSF,
            'employeeCount' => count($nssfData)
        ];
    }

    /**
     * Get NHIF Report Data
     */
    private function getNHIFReportData($period)
    {
        $payrollData = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->where('payrolls.period', $period)
            ->where('payrolls.status', 'Processed')
            ->select(
                'payrolls.*',
                'employees.position',
                'employees.account_number',
                'employees.nhif_number',
                'employees.name as employee_name'
            )
            ->get();

        $nhifData = [];
        $totalNHIF = 0;

        foreach ($payrollData as $payroll) {
            $grossSalary = $payroll->total_amount;
            $employeeNHIF = $this->calculateNHIF($grossSalary);

            $nhifData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'position' => $payroll->position ?? 'N/A',
                'nhif_number' => $payroll->nhif_number ?? 'N/A',
                'gross_salary' => $grossSalary,
                'nhif_amount' => $employeeNHIF,
                'account_number' => $payroll->account_number ?? 'N/A'
            ];

            $totalNHIF += $employeeNHIF;
        }

        return [
            'nhifData' => $nhifData,
            'totalNHIF' => $totalNHIF,
            'employeeCount' => count($nhifData)
        ];
    }

    /**
     * Get Year End Summary Data - Jumla ya mwaka mzima
     */
    private function getYearEndSummaryData($year)
    {
        // Pata payrolls zote za mwaka husika
        $payrollData = DB::table('payrolls')
            ->join('employees', 'payrolls.employee_id', '=', 'employees.employee_id')
            ->whereYear('payrolls.created_at', $year)
            ->where('payrolls.status', 'Processed')
            ->select(
                'payrolls.*',
                'employees.position',
                'employees.account_number',
                'employees.name as employee_name',
                'employees.nssf_number',
                'employees.nhif_number',
                'employees.tin_number'
            )
            ->get();

        // Group data kwa kila mfanyakazi
        $employeeYearlyData = [];
        
        foreach ($payrollData as $payroll) {
            $employeeId = $payroll->employee_id;
            
            if (!isset($employeeYearlyData[$employeeId])) {
                $employeeYearlyData[$employeeId] = [
                    'employee' => [
                        'name' => $payroll->employee_name,
                        'position' => $payroll->position ?? 'N/A',
                        'account_number' => $payroll->account_number ?? 'N/A',
                        'nssf_number' => $payroll->nssf_number ?? 'N/A',
                        'nhif_number' => $payroll->nhif_number ?? 'N/A',
                        'tin_number' => $payroll->tin_number ?? 'N/A'
                    ],
                    'total_basic_salary' => 0,
                    'total_allowances' => 0,
                    'total_gross_salary' => 0,
                    'total_nssf' => 0,
                    'total_nhif' => 0,
                    'total_payee' => 0,
                    'total_other_deductions' => 0,
                    'total_net_salary' => 0,
                    'total_employer_nssf' => 0,
                    'total_employer_wcf' => 0,
                    'total_employer_sdl' => 0,
                    'months_worked' => 0
                ];
            }

            // Calculate statutory deductions for each payroll
            $grossSalary = $payroll->total_amount;
            $nssf = $this->calculateNSSF($grossSalary);
            $nhif = $this->calculateNHIF($grossSalary);
            $payee = $this->calculatePAYE($grossSalary, $nssf);
            $otherDeductions = max(0, $payroll->deductions - ($nssf + $nhif + $payee));
            $employerNSSF = $this->calculateNSSFEmployer($grossSalary);
            $employerWCF = $this->calculateWCF($grossSalary);
            $employerSDL = $this->calculateSDL($grossSalary);

            // Accumulate totals
            $employeeYearlyData[$employeeId]['total_basic_salary'] += $payroll->base_salary;
            $employeeYearlyData[$employeeId]['total_allowances'] += $payroll->allowances ?? 0;
            $employeeYearlyData[$employeeId]['total_gross_salary'] += $grossSalary;
            $employeeYearlyData[$employeeId]['total_nssf'] += $nssf;
            $employeeYearlyData[$employeeId]['total_nhif'] += $nhif;
            $employeeYearlyData[$employeeId]['total_payee'] += $payee;
            $employeeYearlyData[$employeeId]['total_other_deductions'] += $otherDeductions;
            $employeeYearlyData[$employeeId]['total_net_salary'] += $payroll->net_salary;
            $employeeYearlyData[$employeeId]['total_employer_nssf'] += $employerNSSF;
            $employeeYearlyData[$employeeId]['total_employer_wcf'] += $employerWCF;
            $employeeYearlyData[$employeeId]['total_employer_sdl'] += $employerSDL;
            $employeeYearlyData[$employeeId]['months_worked']++;
        }

        // Convert to array for template
        $employeeData = [];
        $grandTotals = [
            'total_basic_salary' => 0,
            'total_allowances' => 0,
            'total_gross_salary' => 0,
            'total_nssf' => 0,
            'total_nhif' => 0,
            'total_payee' => 0,
            'total_other_deductions' => 0,
            'total_net_salary' => 0,
            'total_employer_nssf' => 0,
            'total_employer_wcf' => 0,
            'total_employer_sdl' => 0,
            'total_employer_contributions' => 0,
            'employee_count' => count($employeeYearlyData),
            'total_months_worked' => 0
        ];

        $index = 1;
        foreach ($employeeYearlyData as $employeeId => $data) {
            $totalEmployerContributions = $data['total_employer_nssf'] + $data['total_employer_wcf'] + $data['total_employer_sdl'];
            
            $employeeData[] = [
                'na' => $index++,
                'name' => $data['employee']['name'],
                'position' => $data['employee']['position'],
                'nssf_number' => $data['employee']['nssf_number'],
                'tin_number' => $data['employee']['tin_number'],
                'months_worked' => $data['months_worked'],
                'total_basic_salary' => $data['total_basic_salary'],
                'total_allowances' => $data['total_allowances'],
                'total_gross_salary' => $data['total_gross_salary'],
                'total_nssf' => $data['total_nssf'],
                'total_nhif' => $data['total_nhif'],
                'total_payee' => $data['total_payee'],
                'total_other_deductions' => $data['total_other_deductions'],
                'total_net_salary' => $data['total_net_salary'],
                'total_employer_nssf' => $data['total_employer_nssf'],
                'total_employer_wcf' => $data['total_employer_wcf'],
                'total_employer_sdl' => $data['total_employer_sdl'],
                'total_employer_contributions' => $totalEmployerContributions,
                'account' => $data['employee']['account_number']
            ];

            // Accumulate grand totals
            $grandTotals['total_basic_salary'] += $data['total_basic_salary'];
            $grandTotals['total_allowances'] += $data['total_allowances'];
            $grandTotals['total_gross_salary'] += $data['total_gross_salary'];
            $grandTotals['total_nssf'] += $data['total_nssf'];
            $grandTotals['total_nhif'] += $data['total_nhif'];
            $grandTotals['total_payee'] += $data['total_payee'];
            $grandTotals['total_other_deductions'] += $data['total_other_deductions'];
            $grandTotals['total_net_salary'] += $data['total_net_salary'];
            $grandTotals['total_employer_nssf'] += $data['total_employer_nssf'];
            $grandTotals['total_employer_wcf'] += $data['total_employer_wcf'];
            $grandTotals['total_employer_sdl'] += $data['total_employer_sdl'];
            $grandTotals['total_employer_contributions'] += $totalEmployerContributions;
            $grandTotals['total_months_worked'] += $data['months_worked'];
        }

        return [
            'employees' => $employeeData,
            'totals' => $grandTotals,
            'year' => $year
        ];
    }

    // ========== KANUNI ZA TANZANIA ZA HESABU ==========

    /**
     * Calculate NSSF Contribution (Employee) - Kulingana na sheria za Tanzania
     */
    private function calculateNSSF($grossSalary)
    {
        // NSSF inalipwa kwa mshahara hadi TZS 2,000,000 kwa kiwango cha 10%
        $nssfBase = min($grossSalary, self::NSSF_LIMIT);
        return $nssfBase * self::NSSF_RATE_EMPLOYEE;
    }

    /**
     * Calculate NSSF Employer Contribution
     */
    private function calculateNSSFEmployer($grossSalary)
    {
        $nssfBase = min($grossSalary, self::NSSF_LIMIT);
        return $nssfBase * self::NSSF_RATE_EMPLOYER;
    }

    /**
     * Calculate NHIF Contribution - Kulingana na kanuni za Tanzania
     */
    private function calculateNHIF($salary)
    {
        foreach (self::NHIF_RATES as $limit => $amount) {
            if ($salary <= $limit) {
                return $amount;
            }
        }
        return 2400;
    }

    /**
     * Calculate PAYE (Pay As You Earn) - Kulingana na vikwazo vya Tanzania
     */
    private function calculatePAYE($grossSalary, $nssfDeduction)
    {
        // Taxable income = Gross Salary - NSSF
        $taxableIncome = $grossSalary - $nssfDeduction;

        $tax = 0;
        $previousLimit = 0;

        foreach (self::TAX_BRACKETS as $bracket) {
            if ($taxableIncome > $previousLimit) {
                $bracketAmount = min($taxableIncome - $previousLimit, $bracket['limit'] - $previousLimit);
                $tax += $bracketAmount * $bracket['rate'];
                $previousLimit = $bracket['limit'];
            }
        }

        return $tax;
    }

    /**
     * Calculate WCF (Workers Compensation Fund) - Employer pays
     */
    private function calculateWCF($grossSalary)
    {
        return $grossSalary * self::WCF_RATE;
    }

    /**
     * Calculate SDL (Skills Development Levy) - Employer pays
     */
    private function calculateSDL($grossSalary)
    {
        return $grossSalary * self::SDL_RATE;
    }

    /**
     * Get deduction breakdown for payslip
     */
    private function getDeductionBreakdown($payroll, $employee)
    {
        if (!$payroll) return [];

        $grossSalary = $payroll->total_amount;
        $nssf = $this->calculateNSSF($grossSalary);
        $nhif = $this->calculateNHIF($grossSalary);
        $paye = $this->calculatePAYE($grossSalary, $nssf);

        return [
            'nssf' => $nssf,
            'nhif' => $nhif,
            'paye' => $paye,
            'other_deductions' => max(0, $payroll->deductions - ($nssf + $nhif + $paye)),
            'employer_contributions' => [
                'nssf' => $this->calculateNSSFEmployer($grossSalary),
                'wcf' => $this->calculateWCF($grossSalary),
                'sdl' => $this->calculateSDL($grossSalary)
            ]
        ];
    }

    /**
     * Generate CSV Data - Kulingana na schema ya PDF
     */
    private function generateCsvData($reportData, $reportType)
    {
        $csv = "";

        if ($reportType === 'payroll_summary') {
            // Headers kama kwenye PDF yako
            $csv .= "NA.,JINA LA MFANYAKAZI,CHEO,BASIC SALARY,ALLOWANCE,GROSS SALARY,NSSF,PAYEE,BIMA,BODI MIKOPO,TUICO,MADENI NAFSIA,TAKE HOME,AKAUNTI\n";

            foreach ($reportData['employees'] as $employee) {
                $csv .= "{$employee['na']},";
                $csv .= "\"{$employee['name']}\",";
                $csv .= "\"{$employee['position']}\",";
                $csv .= "{$employee['basic_salary']},";
                $csv .= "{$employee['allowance']},";
                $csv .= "{$employee['gross_salary']},";
                $csv .= "{$employee['nssf']},";
                $csv .= "{$employee['payee']},";
                $csv .= "{$employee['bima']},";
                $csv .= "{$employee['bodi_mikopo']},";
                $csv .= "{$employee['tuico']},";
                $csv .= "{$employee['madeni_nafsia']},";
                $csv .= "{$employee['take_home']},";
                $csv .= "\"{$employee['account']}\"\n";
            }

            // Add totals row
            $totals = $reportData['totals'];
            $csv .= "TOTAL,,,{$totals['total_basic_salary']},{$totals['total_allowance']},{$totals['total_gross_salary']},{$totals['total_nssf']},{$totals['total_payee']},{$totals['total_bima']},{$totals['total_bodi_mikopo']},{$totals['total_tuico']},{$totals['total_madeni_nafsia']},{$totals['total_take_home']},{$totals['employee_count']} Wafanyakazi\n";
        }
        elseif ($reportType === 'year_end_summary') {
            // Employee Contributions CSV
            $csv .= "RIPOTI YA MWISHO WA MWAKA - {$reportData['year']}\n\n";
            $csv .= "MICHANGO YA WAFANYAKAZI\n";
            $csv .= "NA.,JINA LA MFANYAKAZI,CHEO,NSSF NO.,TIN NO.,MIEZI,MSHAHARA MSINGI,ALLOWANCE,MSHAHARA JUMLA,NSSF,NHIF,PAYEE,MADENI NAFSIA,TAKE HOME,AKAUNTI\n";

            foreach ($reportData['employees'] as $employee) {
                $csv .= "{$employee['na']},";
                $csv .= "\"{$employee['name']}\",";
                $csv .= "\"{$employee['position']}\",";
                $csv .= "\"{$employee['nssf_number']}\",";
                $csv .= "\"{$employee['tin_number']}\",";
                $csv .= "{$employee['months_worked']},";
                $csv .= "{$employee['total_basic_salary']},";
                $csv .= "{$employee['total_allowances']},";
                $csv .= "{$employee['total_gross_salary']},";
                $csv .= "{$employee['total_nssf']},";
                $csv .= "{$employee['total_nhif']},";
                $csv .= "{$employee['total_payee']},";
                $csv .= "{$employee['total_other_deductions']},";
                $csv .= "{$employee['total_net_salary']},";
                $csv .= "\"{$employee['account']}\"\n";
            }

            $totals = $reportData['totals'];
            $csv .= "JUMLA,,,,{$totals['total_months_worked']},{$totals['total_basic_salary']},{$totals['total_allowances']},{$totals['total_gross_salary']},{$totals['total_nssf']},{$totals['total_nhif']},{$totals['total_payee']},{$totals['total_other_deductions']},{$totals['total_net_salary']},{$totals['employee_count']} Wafanyakazi\n\n";

            // Employer Contributions CSV
            $csv .= "MICHANGO YA MWAJIRI\n";
            $csv .= "NA.,JINA LA MFANYAKAZI,NSSF NO.,MSHAHARA JUMLA,NSSF (MWAJIRI),WCF (MWAJIRI),SDL (MWAJIRI),JUMLA MWAJIRI,JUMLA WOTE,AKAUNTI\n";

            foreach ($reportData['employees'] as $employee) {
                $csv .= "{$employee['na']},";
                $csv .= "\"{$employee['name']}\",";
                $csv .= "\"{$employee['nssf_number']}\",";
                $csv .= "{$employee['total_gross_salary']},";
                $csv .= "{$employee['total_employer_nssf']},";
                $csv .= "{$employee['total_employer_wcf']},";
                $csv .= "{$employee['total_employer_sdl']},";
                $csv .= "{$employee['total_employer_contributions']},";
                $csv .= ($employee['total_employer_contributions'] + $employee['total_nssf'] + $employee['total_nhif'] + $employee['total_payee']) . ",";
                $csv .= "\"{$employee['account']}\"\n";
            }

            $csv .= "JUMLA MWAJIRI,,,{$totals['total_gross_salary']},{$totals['total_employer_nssf']},{$totals['total_employer_wcf']},{$totals['total_employer_sdl']},{$totals['total_employer_contributions']}," . ($totals['total_employer_contributions'] + $totals['total_nssf'] + $totals['total_nhif'] + $totals['total_payee']) . ",{$totals['employee_count']} Wafanyakazi\n";
        }

        return $csv;
    }

    /**
     * Get Batch Report View
     */
    private function getBatchReportView($reportType)
    {
        $views = [
            'payroll_summary' => 'reports.payroll-summary',
            'tax_report' => 'reports.tax-report',
            'nssf_report' => 'reports.nssf-report',
            'nhif_report' => 'reports.nhif-report',
            'year_end_summary' => 'reports.year-end-summary',
        ];

        return $views[$reportType] ?? 'reports.default';
    }

    /**
     * Get Individual Report View
     */
    private function getIndividualReportView($reportType)
    {
        $views = [
            'payslip' => 'reports.payslip-individual',
        ];

        return $views[$reportType] ?? 'reports.default';
    }

    private function cleanupOldReports()
    {
        try {
            $files = Storage::disk(self::STORAGE_DISK)->files(self::REPORTS_PATH);
            $threshold = now()->subDays(7)->timestamp;

            foreach ($files as $file) {
                if (Storage::disk(self::STORAGE_DISK)->lastModified($file) < $threshold) {
                    Storage::disk(self::STORAGE_DISK)->delete($file);
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup old reports: ' . $e->getMessage());
        }
    }

    /**
     * Get Next Batch Number
     */
    private function getNextBatchNumber($reportType, $period)
    {
        $lastBatch = Report::where('type', $reportType)
            ->where('period', $period)
            ->whereNotNull('batch_number')
            ->orderBy('batch_number', 'desc')
            ->first();

        return ($lastBatch->batch_number ?? 0) + 1;
    }

    /**
     * Download Report
     */
    public function download($id)
    {
        $user = Auth::user();
        $report = Report::findOrFail($id);

        // Ukaguzi wa ruhusa
        if (strtolower($user->role) === 'employee') {
            // Employee anaweza kudownload payslip yake tu
            if ($report->type !== 'payslip') {
                abort(403, 'Unauthorized to download this report.');
            }

            $employeeId = $user->employee_id ?? $user->employee->employee_id ?? null;
            if ($report->employee_id !== $employeeId) {
                abort(403, 'Unauthorized to download this report.');
            }
        }

        // Try different file extensions
        $baseFilename = "{$report->report_id}_{$report->type}_{$report->period}";
        $extensions = ['pdf', 'csv'];

        foreach ($extensions as $ext) {
            $filePath = self::REPORTS_PATH . '/' . $baseFilename . '.' . $ext;
            if (Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                return Storage::disk(self::STORAGE_DISK)->download($filePath, $baseFilename . '.' . $ext);
            }
        }

        return redirect()->route('reports')->with('error', 'Report file not found.');
    }

    /**
     * Delete Report
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!in_array(strtolower($user->role), ['admin', 'hr'])) {
            abort(403, 'Unauthorized access.');
        }

        $report = Report::findOrFail($id);

        // Delete associated files
        $baseFilename = "{$report->report_id}_{$report->type}_{$report->period}";
        $extensions = ['pdf', 'csv'];

        foreach ($extensions as $ext) {
            $filePath = self::REPORTS_PATH . '/' . $baseFilename . '.' . $ext;
            if (Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
                Storage::disk(self::STORAGE_DISK)->delete($filePath);
            }
        }

        $report->delete();

        return redirect()->route('reports')->with('success', 'Report deleted successfully.');
    }
}