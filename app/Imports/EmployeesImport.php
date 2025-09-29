<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['name']) && empty($row['email'])) {
                continue;
            }

            // Check if email already exists
            if (Employee::where('email', $row['email'])->exists()) {
                continue; // Skip duplicate emails
            }

            // Generate employee ID
            $employeeId = "EMP-" . strtoupper(Str::random(8));
            
            // Generate password
            $nameParts = explode(' ', $row['name']);
            $lastName = end($nameParts);
            $password = Hash::make(strtolower($lastName ?: 'password123'));

            // Create employee
            Employee::create([
                'employee_id' => $employeeId,
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $password,
                'phone' => $row['phone'] ?? null,
                'department' => $row['department'],
                'position' => $row['position'],
                'employment_type' => $row['employment_type'],
                'hire_date' => $row['hire_date'],
                'base_salary' => $row['base_salary'],
                'role' => $row['role'],
                'status' => 'active',
                'gender' => $row['gender'] ?? null,
                'dob' => $row['dob'] ?? null,
                'nationality' => $row['nationality'] ?? null,
                'address' => $row['address'] ?? null,
                'contract_end_date' => $row['contract_end_date'] ?? null,
                'bank_name' => $row['bank_name'] ?? null,
                'account_number' => $row['account_number'] ?? null,
                'nssf_number' => $row['nssf_number'] ?? null,
                'tin_number' => $row['tin_number'] ?? null,
                'nhif_number' => $row['nhif_number'] ?? null,
            ]);
        }
    }
}