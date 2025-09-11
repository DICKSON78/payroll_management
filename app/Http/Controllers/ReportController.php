<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

class ReportController extends Controller
{
    private const REPORT_TYPES = [
        'payslip', 'payroll_summary', 'tax_report', 'nssf_report',
        'nhif_report', 'wcf_report', 'sdl_report', 'year_end_summary'
    ];
    private const EXPORT_FORMATS = ['pdf', 'excel'];
    private const STORAGE_DISK = 'public';
    private const REPORTS_PATH = 'reports';

    private function checkAuthorization()
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access reports.');
        }

        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'hr'])) {
            return redirect()->back()->with('error', 'Unauthorized access. Admin or HR role required.');
        }

        return null;
    }

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

    public function index(Request $request)
    {
        if ($authError = $this->checkAuthorization()) {
            return $authError;
        }

        $search = trim($request->query('search', ''));
        $query = Report::with('employee')->where('generated_by', Auth::id())->where('status', 'completed')->latest();

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
        return view('dashboard.report', compact('reports', 'employees', 'settings'));
    }

    public function generate(Request $request)
    {
        if ($authError = $this->checkAuthorization()) {
            return $authError;
        }

        Log::info('Report generation request: ' . json_encode($request->all()));

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

        $report = Report::create([
            'report_id' => 'RPT-' . strtoupper(uniqid()),
            'type' => $reportType,
            'period' => $reportPeriod,
            'employee_id' => $employeeId,
            'export_format' => $exportFormat,
            'generated_by' => Auth::id(),
            'status' => 'pending',
        ]);

        $query = Payslip::where('period', $isYearlyReport ? 'like' : '=', $isYearlyReport ? "{$reportPeriod}%" : $reportPeriod);
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }
        $data = $query->with('employee')->get();
        $employees = $employeeId ? Employee::where('id', $employeeId)->get() : Employee::whereIn('id', $data->pluck('employee_id'))->get();
        $settings = Setting::first() ?? [
            'company_name' => 'Default Company',
            'currency' => 'TZS',
            'tax_rate' => 30.00,
            'payroll_cycle' => 'Monthly',
            'company_logo' => null,
        ];

        try {
            $this->cleanupOldReports();

            $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
            $filePath = self::REPORTS_PATH . '/' . $filename;

            if ($exportFormat === 'pdf') {
                $viewMap = [
                    'payslip' => 'reports.payslip',
                    'payroll_summary' => 'reports.payroll_summary',
                    'tax_report' => 'reports.tax_form',
                    'nssf_report' => 'reports.nssf_form',
                    'nhif_report' => 'reports.nhif_form',
                    'wcf_report' => 'reports.wcf_form',
                    'sdl_report' => 'reports.sdl_form',
                    'year_end_summary' => 'reports.year_end_summary',
                ];
                $view = $viewMap[$reportType] ?? 'reports.default';
                $pdf = Pdf::loadView($view, compact('data', 'reportPeriod', 'employees', 'settings'));
                Storage::disk(self::STORAGE_DISK)->put($filePath, $pdf->output());
            } elseif ($exportFormat === 'excel') {
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
                $exportClass = $exportMap[$reportType] ?? PayslipExport::class;
                Excel::store(new $exportClass($data, $reportPeriod, $employees, $settings), $filePath, self::STORAGE_DISK);
            }

            $report->update(['status' => 'completed']);
            Log::info("Report generated: {$report->report_id}, File: {$filePath}");
            return redirect()->route('reports')->with('success', 'Report generated successfully!');
        } catch (\Exception $e) {
            $report->update(['status' => 'failed']);
            Log::error('Report generation failed: ' . $e->getMessage(), ['report_id' => $report->report_id]);
            $report->delete();
            return redirect()->route('reports')->with('error', 'Failed to generate report: ' . $e->getMessage());
        }
    }

    public function download($id)
    {
        if ($authError = $this->checkAuthorization()) {
            return $authError;
        }

        $report = Report::where('generated_by', Auth::id())
            ->where('status', 'completed')
            ->findOrFail($id);

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if (!Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
            Log::error("Report file not found: {$filePath}", [
                'report_id' => $report->report_id,
                'user_id' => Auth::id(),
                'file_path' => storage_path('app/public/' . $filePath)
            ]);
            return redirect()->route('reports')->with('error', 'Report file not found. It may have been deleted or not generated.');
        }

        Log::info("Downloading report: {$report->report_id}, File: {$filePath}");
        return Storage::disk(self::STORAGE_DISK)->download($filePath, $filename);
    }

    public function destroy($id)
    {
        if ($authError = $this->checkAuthorization()) {
            return $authError;
        }

        $report = Report::where('generated_by', Auth::id())
            ->where('status', 'completed')
            ->findOrFail($id);

        $filename = "{$report->report_id}_{$report->type}_{$report->period}.{$report->export_format}";
        $filePath = self::REPORTS_PATH . '/' . $filename;

        if (Storage::disk(self::STORAGE_DISK)->exists($filePath)) {
            Storage::disk(self::STORAGE_DISK)->delete($filePath);
            Log::info("Deleted report file: {$filePath}");
        }

        $report->delete();
        Log::info("Report deleted: {$report->report_id}");
        return redirect()->route('reports')->with('success', 'Report deleted successfully!');
    }
}