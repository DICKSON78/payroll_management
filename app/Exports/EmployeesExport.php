<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromArray, WithHeadings
{
    protected $employees;

    public function __construct(array $employees)
    {
        $this->employees = $employees;
    }

    public function array(): array
    {
        return $this->employees;
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Name', 
            'Email',
            'Phone',
            'Gender',
            'Date of Birth',
            'Nationality',
            'Address',
            'Department',
            'Position',
            'Role',
            'Employment Type',
            'Hire Date',
            'Contract End Date',
            'Base Salary',
            'Total Allowances',
            'Bank Name',
            'Account Number',
            'NSSF Number',
            'TIN Number',
            'NHIF Number',
            'Status',
            'Created At'
        ];
    }
}