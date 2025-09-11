<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Payslip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaxReportExport implements FromCollection, WithHeadings
{
    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function collection()
    {
        $query = Payslip::where('period', $this->report->period);
        if ($this->report->employee_id) {
            $query->where('employee_id', $this->report->employee_id);
        }
        return $query->get()->map(function ($payslip) {
            return [
                'Employee' => $payslip->employee->name ?? 'N/A',
                'Period' => $payslip->period,
                'Tax Deducted' => $payslip->tax_deducted ?? 0,
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee', 'Period', 'Tax Deducted'];
    }
}