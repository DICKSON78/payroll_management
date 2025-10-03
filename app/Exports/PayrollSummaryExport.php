<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PayrollSummaryExport implements FromCollection, WithHeadings, WithTitle
{
    protected $reportData;

    public function __construct($reportData)
    {
        $this->reportData = $reportData;
    }

    public function collection()
    {
        $data = collect();
        
        // Add headers
        $headers = [
            'NA.', 'JINA LA MFANYAKAZI', 'CHEO', 'BASIC SALARY AS PER CONTRACT', 
            'ALLOWANCE', 'GROSS SALARY', 'MWANZO WA KUAJIRIWA', 'MWISHO WA MKATABA',
            '', 'BASIC/SALARY', 'NSSF', 'PAYEE', 'BIMA', 'BODI MIKOPO', 'TUICO', 
            'MADENI NAFSIA', 'TAKE HOME', 'AKAUNTI'
        ];
        $data->push($headers);
        
        // Add employee data
        $employeeNumber = 1;
        foreach ($this->reportData['employees'] as $employee) {
            $grossSalary = ($employee['basic_salary'] ?? 0) + ($employee['allowance'] ?? 0);
            
            $row = [
                $employeeNumber++,
                $employee['name'] ?? '',
                $employee['position'] ?? '',
                $employee['basic_salary'] ?? 0,
                $employee['allowance'] ?? 0,
                $grossSalary,
                $employee['start_date'] ?? '',
                $employee['end_date'] ?? '',
                '', // Empty column
                $employee['basic_salary_actual'] ?? $employee['basic_salary'] ?? 0,
                $employee['nssf'] ?? 0,
                $employee['payee'] ?? 0,
                $employee['insurance'] ?? 0,
                $employee['loan_board'] ?? 0,
                $employee['tuico'] ?? 0,
                $employee['personal_debts'] ?? 0,
                $employee['take_home'] ?? 0,
                $employee['account_number'] ?? ''
            ];
            $data->push($row);
        }
        
        return $data;
    }

    public function headings(): array
    {
        return [];
    }

    public function title(): string
    {
        return $this->reportData['sheet_name'] ?? 'Sheet3';
    }
}