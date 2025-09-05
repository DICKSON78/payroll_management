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
    public function index()
    {
        $reports = Report::with('employee')->get();
        $settings = Setting::first() ?? ['company_name' => 'Default Company', 'currency' => 'TZS', 'tax_rate' => 30.00, 'payroll_cycle' => 'Monthly', 'company_logo' => null];
        $employees = Employee::all();
        Log::info('Reports fetched: ' . $reports->count() . ', Employees fetched: ' . $employees->count());
        return view('dashboard.report', compact('reports', 'employees'));
    }

    public function generate(Request $request)
    {
        Log::info('Report generation request: ' . json_encode($request->all()));

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:payslip,payroll_summary,tax_report,nssf_report,nhif_report,year_end_summary',
            'report_period' => 'required|date_format:Y-m',
            'employee_id' => 'nullable|exists:employees,id',
            'export_format' => 'required|in:pdf,excel',
        ]);

        if ($validator->fails()) {
            Log::error('Validation errors: ' . json_encode($validator->errors()));
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $period = $request->report_period;
        $reportType = $request->report_type;
        $employeeId = $request->employee_id;
        $exportFormat = $request->export_format;

        // Fetch data based on report type
        $query = Payslip::with('employee')->where('period', $period);
        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $data = [];
        switch ($reportType) {
            case 'payslip':
            case 'payroll_summary':
                $data = $query->get();
                break;
            case 'tax_report':
                $data = $query->select('employee_id', 'paye')->get();
                break;
            case 'nssf_report':
                $data = $query->select('employee_id', 'nssf')->get();
                break;
            case 'nhif_report':
                $data = $query->select('employee_id', 'nhif')->get();
                break;
            case 'year_end_summary':
                $data = Payslip::with('employee')
                    ->whereYear('period', substr($period, 0, 4))
                    ->when($employeeId, function ($query, $employeeId) {
                        return $query->where('employee_id', $employeeId);
                    })
                    ->get();
                break;
        }

        // Generate unique report_id
        $lastReport = Report::orderBy('id', 'desc')->first();
        $nextId = $lastReport ? $lastReport->id + 1 : 1;
        $reportId = 'RPT-' . str_pad($nextId, 3, '0', STR_PAD_LEFT);

        // Store report in database
        $report = Report::create([
            'report_id' => $reportId,
            'type' => $reportType,
            'period' => $period,
            'employee_id' => $employeeId,
            'export_format' => $exportFormat,
        ]);

        Log::info('Report created: ' . json_encode($report));

        // Generate report based on export format
        try {
            if ($exportFormat === 'pdf') {
                $pdf = Pdf::loadView('reports.' . $reportType, ['data' => $data, 'period' => $period]);
                return $pdf->download($reportId . '_' . $period . '.pdf');
            } else {
                return Excel::download(new PayslipsExport($data, $reportType, $period), $reportId . '_' . $period . '.xlsx');
            }
        } catch (\Exception $e) {
            Log::error('Error generating report: ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Failed to generate report. Please try again.');
        }
    }

    public function download($id, $format)
    {
        $report = Report::findOrFail($id);
        Log::info('Downloading report ID: ' . $report->id . ', Format: ' . $format);

        $query = Payslip::with('employee')->where('period', $report->period);
        if ($report->employee_id) {
            $query->where('employee_id', $report->employee_id);
        }

        $data = [];
        switch ($report->type) {
            case 'payslip':
            case 'payroll_summary':
                $data = $query->get();
                break;
            case 'tax_report':
                $data = $query->select('employee_id', 'paye')->get();
                break;
            case 'nssf_report':
                $data = $query->select('employee_id', 'nssf')->get();
                break;
            case 'nhif_report':
                $data = $query->select('employee_id', 'nhif')->get();
                break;
            case 'year_end_summary':
                $data = Payslip::with('employee')
                    ->whereYear('period', substr($report->period, 0, 4))
                    ->when($report->employee_id, function ($query, $employeeId) {
                        return $query->where('employee_id', $employeeId);
                    })
                    ->get();
                break;
        }

        try {
            if ($format === 'pdf') {
                $pdf = Pdf::loadView('reports.' . $report->type, ['data' => $data, 'period' => $report->period]);
                return $pdf->download($report->report_id . '_' . $report->period . '.pdf');
            } elseif ($format === 'excel') {
                return Excel::download(new PayslipsExport($data, $report->type, $report->period), $report->report_id . '_' . $report->period . '.xlsx');
            }
            return redirect()->route('reports.index')->with('error', 'Invalid format');
        } catch (\Exception $e) {
            Log::error('Error downloading report: ' . $e->getMessage());
            return redirect()->route('reports.index')->with('error', 'Failed to download report. Please try again.');
        }
    }
}
