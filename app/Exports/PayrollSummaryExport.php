<?php

namespace App\Exports;

use App\Models\Employee;
use App\Models\Payslip;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PayrollSummaryExport implements FromCollection, WithHeadings
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
                'Gross Salary' => $payslip->gross_salary,
                'Net Salary' => $payslip->net_salary,
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee', 'Period', 'Gross Salary', 'Net Salary'];
    }
}