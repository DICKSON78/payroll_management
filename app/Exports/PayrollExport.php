<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class PayrollExport implements FromCollection, WithHeadings
{
    protected $payslips;
    protected $settings;
    protected $period;

    public function __construct($payslips, $settings, $period)
    {
        $this->payslips = $payslips;
        $this->settings = $settings;
        $this->period = $period;
    }

    public function collection()
    {
        return $this->payslips->map(function ($payslip) {
            return [
                'Employee ID' => $payslip->employee->employee_id ?? 'N/A',
                'Name' => $payslip->employee->name ?? 'N/A',
                'Department' => $payslip->employee->department ?? 'N/A',
                'Base Salary' => $payslip->base_salary,
                'Allowances' => $payslip->housing_allowance + $payslip->transport_allowance + $payslip->medical_allowance,
                'Gross Salary' => $payslip->gross_salary,
                'PAYE' => $payslip->paye,
                'NSSF' => $payslip->nssf,
                'NHIF' => $payslip->nhif,
                'WCF' => $payslip->wcf,
                'SDL' => $payslip->sdl,
                'TUICO' => $payslip->tuico,
                'HESLB' => $payslip->heslb,
                'Total Deductions' => $payslip->total_deductions,
                'Net Salary' => $payslip->net_salary,
                'Period' => $this->period->format('F Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Name',
            'Department',
            'Base Salary (TZS)',
            'Allowances (TZS)',
            'Gross Salary (TZS)',
            'PAYE (TZS)',
            'NSSF (TZS)',
            'NHIF (TZS)',
            'WCF (TZS)',
            'SDL (TZS)',
            'TUICO (TZS)',
            'HESLB (TZS)',
            'Total Deductions (TZS)',
            'Net Salary (TZS)',
            'Period',
        ];
    }
}
