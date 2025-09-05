<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PayslipsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $data;
    protected $reportType;
    protected $period;

    public function __construct($data, $reportType, $period)
    {
        $this->data = $data;
        $this->reportType = $reportType;
        $this->period = $period;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        switch ($this->reportType) {
            case 'payslip':
            case 'payroll_summary':
                return ['Employee', 'Department', 'Gross Salary', 'NSSF', 'PAYE', 'NHIF', 'Net Salary', 'Status'];
            case 'tax_report':
                return ['Employee', 'PAYE'];
            case 'nssf_report':
                return ['Employee', 'NSSF'];
            case 'nhif_report':
                return ['Employee', 'NHIF'];
            case 'year_end_summary':
                return ['Employee', 'Period', 'Gross Salary', 'NSSF', 'PAYE', 'NHIF', 'Net Salary'];
            default:
                return [];
        }
    }

    public function map($row): array
    {
        switch ($this->reportType) {
            case 'payslip':
            case 'payroll_summary':
                return [
                    $row->employee->name ?? 'N/A',
                    $row->employee->department ?? 'N/A',
                    $row->gross_salary,
                    $row->nssf,
                    $row->paye,
                    $row->nhif,
                    $row->net_salary,
                    $row->status,
                ];
            case 'tax_report':
                return [
                    $row->employee->name ?? 'N/A',
                    $row->paye,
                ];
            case 'nssf_report':
                return [
                    $row->employee->name ?? 'N/A',
                    $row->nssf,
                ];
            case 'nhif_report':
                return [
                    $row->employee->name ?? 'N/A',
                    $row->nhif,
                ];
            case 'year_end_summary':
                return [
                    $row->employee->name ?? 'N/A',
                    $row->period,
                    $row->gross_salary,
                    $row->nssf,
                    $row->paye,
                    $row->nhif,
                    $row->net_salary,
                ];
            default:
                return [];
        }
    }
}