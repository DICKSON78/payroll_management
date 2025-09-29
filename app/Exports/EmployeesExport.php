<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Employee::with('allowances')->get()->map(function ($employee) {
            return [
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'address' => $employee->address,
                'dob' => $employee->dob,
                'hire_date' => $employee->hire_date,
                'employment_type' => $employee->employment_type,
                'status' => $employee->status,
                'base_salary' => $employee->base_salary,
                'allowance_ids' => $employee->allowances->pluck('id')->implode(','),
                'deductions' => $employee->deductions,
                'nssf_number' => $employee->nssf_number,
                'nhif_number' => $employee->nhif_number,
                'tin_number' => $employee->tin_number,
                'department' => $employee->department,
                'role' => $employee->role,
                'position' => $employee->position,
                'bank_name' => $employee->bank_name,
                'account_number' => $employee->account_number,
                'gender' => $employee->gender,
                'nationality' => $employee->nationality,
                'contract_end_date' => $employee->contract_end_date,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Name', 'Email', 'Phone', 'Address', 'DOB (YYYY-MM-DD)',
            'Hire Date (YYYY-MM-DD)', 'Employment Type (Full-Time/Part-Time/Contract)', 'Status (active/inactive/on_leave)',
            'Base Salary', 'Allowance IDs (comma-separated)', 'Deductions', 'NSSF Number', 'NHIF Number',
            'TIN Number', 'Department', 'Role', 'Position', 'Bank Name', 'Account Number',
            'Gender', 'Nationality', 'Contract End Date (YYYY-MM-DD)'
        ];
    }
}