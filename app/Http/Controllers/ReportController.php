<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PayslipsExport;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $reports = Report::with('employee')->paginate(10); // Paginate with 10 items per page
        $employees = Employee::all();
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

    /**
     * Generate a new report.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generate(Request $request)
    {
        Log::info('Report generation request: ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:payslip,payroll_summary,tax_report,nssf_report,nhif_report,wcf_report,sdl_report,year_end_summary',
            'report_period' => 'required|date_format:Y-m',
            'employee_id' => 'nullable|exists:employees,id',
            'export_format' => 'required|in:pdf,excel',
        ]);

        if ($validator->fails()) {
            return redirect()->route('reports')->withErrors($validator)->withInput();
        }

        $reportType = $request->input('report_type');
        $reportPeriod = $request->input('report_period');
        $employeeId = $request->input('employee_id');
        $exportFormat = $request->input('export_format');

        // Fetch data based on report type
        $query = Payslip::where('period', $reportPeriod);
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

        // Create a new report record
        $report = Report::create([
            'report_id' => 'RPT-' . strtoupper(uniqid()),
            'type' => $reportType,
            'period' => $reportPeriod,
            'employee_id' => $employeeId,
            'export_format' => $exportFormat,
            'generated_by' => auth()->id(),
        ]);

        // Generate report based on type and format
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

        try {
            if ($exportFormat === 'pdf') {
                $view = $viewMap[$reportType] ?? 'reports.default';
                $pdf = Pdf::loadView($view, compact('data', 'reportPeriod', 'employees', 'settings'));
                $filename = "{$reportType}_{$reportPeriod}.pdf";
                // Store the PDF temporarily
                $pdf->save(storage_path("app/public/reports/{$report->report_id}.pdf"));
                return redirect()->route('reports')->with('success', 'Report generated successfully!');
            } elseif ($exportFormat === 'excel') {
                $filename = "{$reportType}_{$reportPeriod}.xlsx";
                // Store the Excel file temporarily
                $filePath = "reports/{$report->report_id}.xlsx";
                Excel::store(new PayslipsExport($data, $reportPeriod, $employees, $settings), $filePath, 'public');
                return redirect()->route('reports')->with('success', 'Report generated successfully!');
            }
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage());
            $report->delete(); // Rollback report creation on failure
            return redirect()->route('reports')->with('error', 'Failed to generate report. Please try again.');
        }

        return redirect()->route('reports')->with('error', 'Invalid export format.');
    }

    /**
     * Download a generated report.
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id)
    {
        $report = Report::findOrFail($id);
        $filePath = storage_path("app/public/reports/{$report->report_id}.{$report->export_format}");
        
        if (!file_exists($filePath)) {
            Log::error("Report file not found: {$filePath}");
            return redirect()->route('reports')->with('error', 'Report file not found.');
        }

        Log::info("Downloading report: {$report->report_id}");
        return response()->download($filePath, "{$report->type}_{$report->period}.{$report->export_format}");
    }

    /**
     * Delete a report and its associated file.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $filePath = storage_path("app/public/reports/{$report->report_id}.{$report->export_format}");

        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $report->delete();
        Log::info("Report deleted: {$report->report_id}");
        return redirect()->route('reports')->with('success', 'Report deleted successfully!');
    }
}