<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Payroll;
use App\Models\Attendance;
use App\Models\ComplianceTask;
use App\Models\LeaveRequest;
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
        'report_period' => 'required|date_format:Y-m',
        'employee_id' => 'nullable|exists:employees,id',
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
        $employee = Employee::find($employeeId);
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
        // NOTIFICATION: Kutengeneza ripoti kumeshindwa
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

                // Use simplified PDF options without complex CSS
                $pdf = Pdf::loadView($view, $reportData)
                    ->setPaper('a4', 'landscape')
                    ->setOptions([
                        'isHtml5ParserEnabled' => true,
                        'isRemoteEnabled' => false, // Disable remote to avoid issues
                        'isPhpEnabled' => false,
                        'dpi' => 72, // Lower DPI for better compatibility
                        'defaultFont' => 'helvetica', // Use basic font
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
            $employee = Employee::findOrFail($employeeId);

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
            'period' => $period,
            'batch_number' => $batchNumber,
            'generated_at' => now(),
            'generated_by' => Auth::user()->name,
            'period_display' => Carbon::parse($period . '-01')->format('F Y'),
        ];

        switch ($reportType) {
            case 'payroll_summary':
                return array_merge($baseData, $this->getPayrollSummaryData($period));

            case 'tax_report':
                return array_merge($baseData, $this->getTaxReportData($period));

            case 'nssf_report':
                return array_merge($baseData, $this->getNSSFReportData($period));

            case 'nhif_report':
                return array_merge($baseData, $this->getNHIFReportData($period));

            default:
                return $baseData;
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
                    'deduction_breakdown' => $this->getDeductionBreakdown($payroll)
                ]);

            default:
                return $baseData;
        }
    }

    /**
     * Get Payroll Summary Data
     */
    private function getPayrollSummaryData($period)
    {
        $payrolls = Payroll::with('employee')
            ->where('period', $period)
            ->where('status', 'Processed')
            ->get();

        $employeeData = [];
        $totals = [
            'total_basic_salary_contract' => 0,
            'total_allowance' => 0,
            'total_gross_salary' => 0,
            'total_basic_salary' => 0,
            'total_nssf' => 0,
            'total_payee' => 0,
            'total_bima' => 0,
            'total_bodi_mikopo' => 0,
            'total_tuico' => 0,
            'total_madeni_nafsia' => 0,
            'total_take_home' => 0,
            'employee_count' => $payrolls->count()
        ];

        foreach ($payrolls as $index => $payroll) {
            // Calculate individual deductions based on actual payroll data
            $nssf = $this->calculateNSSFFromPayroll($payroll);
            $nhif = $this->calculateNHIFFromPayroll($payroll);
            $payee = $this->calculateEmployeeTax($payroll);

            // Calculate other deductions (total deductions minus statutory ones)
            $otherDeductions = max(0, $payroll->deductions - ($nssf + $nhif + $payee));

            $employeeData[] = [
                'na' => $index + 1,
                'name' => $payroll->employee_name,
                'position' => $payroll->employee->position ?? 'N/A',
                'basic_salary_contract' => $payroll->base_salary,
                'allowance' => $payroll->allowances ?? 0,
                'gross_salary' => $payroll->total_amount,
                'start_date' => $payroll->employee->hire_date ?? '',
                'end_date' => $payroll->employee->contract_end_date ?? '',
                'basic_salary' => $payroll->base_salary,
                'nssf' => $nssf,
                'payee' => $payee,
                'bima' => $nhif, // Using NHIF as BIMA for now
                'bodi_mikopo' => 0, // Not available in current data
                'tuico' => 0, // Not available in current data
                'madeni_nafsia' => $otherDeductions,
                'take_home' => $payroll->net_salary,
                'account' => $payroll->employee->account_number ?? ''
            ];

            // Update totals
            $totals['total_basic_salary_contract'] += $payroll->base_salary;
            $totals['total_allowance'] += $payroll->allowances ?? 0;
            $totals['total_gross_salary'] += $payroll->total_amount;
            $totals['total_basic_salary'] += $payroll->base_salary;
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
        $payrolls = Payroll::with('employee')
            ->where('period', $period)
            ->where('status', 'Processed')
            ->get();

        $taxData = [];
        $totalTax = 0;

        foreach ($payrolls as $payroll) {
            $employeeTax = $this->calculateEmployeeTax($payroll);

            $taxData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'taxable_income' => $payroll->total_amount - $this->getNonTaxableDeductions($payroll),
                'tax_amount' => $employeeTax,
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
        $payrolls = Payroll::with('employee')
            ->where('period', $period)
            ->where('status', 'Processed')
            ->get();

        $nssfData = [];
        $totalNSSF = 0;

        foreach ($payrolls as $payroll) {
            $employeeNSSF = $this->calculateNSSFFromPayroll($payroll);

            $nssfData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'nssf_number' => $payroll->employee->nssf_number ?? 'N/A',
                'gross_salary' => $payroll->total_amount,
                'nssf_amount' => $employeeNSSF,
            ];

            $totalNSSF += $employeeNSSF;
        }

        return [
            'nssfData' => $nssfData,
            'totalNSSF' => $totalNSSF,
            'employeeCount' => count($nssfData)
        ];
    }

    /**
     * Get NHIF Report Data
     */
    private function getNHIFReportData($period)
    {
        $payrolls = Payroll::with('employee')
            ->where('period', $period)
            ->where('status', 'Processed')
            ->get();

        $nhifData = [];
        $totalNHIF = 0;

        foreach ($payrolls as $payroll) {
            $employeeNHIF = $this->calculateNHIFFromPayroll($payroll);

            $nhifData[] = [
                'employee_name' => $payroll->employee_name,
                'employee_id' => $payroll->employee_id,
                'nhif_number' => $payroll->employee->nhif_number ?? 'N/A',
                'gross_salary' => $payroll->total_amount,
                'nhif_amount' => $employeeNHIF,
            ];

            $totalNHIF += $employeeNHIF;
        }

        return [
            'nhifData' => $nhifData,
            'totalNHIF' => $totalNHIF,
            'employeeCount' => count($nhifData)
        ];
    }

    // ========== HELPER METHODS ==========

    /**
     * Calculate employee tax from payroll record
     */
    private function calculateEmployeeTax($payroll)
    {
        $taxableIncome = $payroll->total_amount - $this->getNonTaxableDeductions($payroll);
        return $this->calculatePAYE($taxableIncome);
    }

    /**
     * Get non-taxable deductions (NSSF)
     */
    private function getNonTaxableDeductions($payroll)
    {
        return $this->calculateNSSFFromPayroll($payroll);
    }

    /**
     * Calculate NSSF from payroll record
     */
    private function calculateNSSFFromPayroll($payroll)
    {
        $nssfRate = 0.10; // 10%
        $nssfLimit = 2000000;
        $nssfBase = min($payroll->total_amount, $nssfLimit);
        return $nssfBase * $nssfRate;
    }

    /**
     * Calculate NHIF from payroll record
     */
    private function calculateNHIFFromPayroll($payroll)
    {
        return $this->calculateNHIF($payroll->total_amount);
    }

    /**
     * Get deduction breakdown for payslip
     */
    private function getDeductionBreakdown($payroll)
    {
        if (!$payroll) return [];

        return [
            'nssf' => $this->calculateNSSFFromPayroll($payroll),
            'nhif' => $this->calculateNHIFFromPayroll($payroll),
            'paye' => $this->calculateEmployeeTax($payroll),
            'other_deductions' => $payroll->deductions -
                ($this->calculateNSSFFromPayroll($payroll) +
                 $this->calculateNHIFFromPayroll($payroll) +
                 $this->calculateEmployeeTax($payroll))
        ];
    }

    /**
     * Calculate PAYE tax
     */
    private function calculatePAYE($taxableIncome)
    {
        $taxBrackets = [
            ['limit' => 270000, 'rate' => 0.00],
            ['limit' => 520000, 'rate' => 0.08],
            ['limit' => 760000, 'rate' => 0.20],
            ['limit' => 1000000, 'rate' => 0.25],
            ['limit' => PHP_FLOAT_MAX, 'rate' => 0.30]
        ];

        $tax = 0;
        $previousLimit = 0;

        foreach ($taxBrackets as $bracket) {
            if ($taxableIncome > $previousLimit) {
                $bracketAmount = min($taxableIncome - $previousLimit, $bracket['limit'] - $previousLimit);
                $tax += $bracketAmount * $bracket['rate'];
                $previousLimit = $bracket['limit'];
            }
        }

        return $tax;
    }

    /**
     * Calculate NHIF amount
     */
    private function calculateNHIF($salary)
    {
        $tiers = [
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
            PHP_INT_MAX => 2000
        ];

        foreach ($tiers as $limit => $amount) {
            if ($salary <= $limit) {
                return $amount;
            }
        }

        return 2000;
    }

    /**
     * Generate CSV Data
     */
    private function generateCsvData($reportData, $reportType)
    {
        $csv = "";

        if ($reportType === 'payroll_summary') {
            // Headers with all required columns
            $csv .= "NA.,JINA LA MFANYAKAZI,CHEO,BASIC SALARY AS PER CONTRACT,ALLOWANCE,GROSS SALARY,START DATE,END DATE,BASIC SALARY,NSSF,PAYEE,BIMA,BODI MIKOPO,TUICO,MADENI NAFSIA,TAKE HOME,AKAUNTI\n";

            foreach ($reportData['employees'] as $employee) {
                $csv .= "{$employee['na']},";
                $csv .= "\"{$employee['name']}\",";
                $csv .= "\"{$employee['position']}\",";
                $csv .= "{$employee['basic_salary_contract']},";
                $csv .= "{$employee['allowance']},";
                $csv .= "{$employee['gross_salary']},";
                $csv .= "\"{$employee['start_date']}\",";
                $csv .= "\"{$employee['end_date']}\",";
                $csv .= "{$employee['basic_salary']},";
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
            $csv .= "TOTAL,{$reportData['totals']['employee_count']} Wafanyakazi,,,,{$reportData['totals']['total_gross_salary']},,,{$reportData['totals']['total_basic_salary']},{$reportData['totals']['total_nssf']},{$reportData['totals']['total_payee']},{$reportData['totals']['total_bima']},{$reportData['totals']['total_bodi_mikopo']},{$reportData['totals']['total_tuico']},{$reportData['totals']['total_madeni_nafsia']},{$reportData['totals']['total_take_home']},\n";
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

    /**
     * Cleanup Old Reports
     */
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

    // NOTIFICATION: Ripoti imefutwa
    return redirect()->route('reports')->with('success', 'Report deleted successfully.');
}
}
