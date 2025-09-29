<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class EmployeesImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        // Find role by name or slug
        $role = Role::where('name', $row['role'])
                    ->orWhere('slug', $row['role'])
                    ->first();

        return new Employee([
            'employee_id' => $row['employee_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? null,
            'gender' => $row['gender'] ?? null,
            'dob' => isset($row['dob']) ? \Carbon\Carbon::parse($row['dob']) : null,
            'nationality' => $row['nationality'] ?? null,
            'address' => $row['address'] ?? null,
            'department' => $row['department'],
            'position' => $row['position'],
            'role_id' => $role ? $role->id : null,
            'employment_type' => $row['employment_type'],
            'hire_date' => \Carbon\Carbon::parse($row['hire_date']),
            'contract_end_date' => isset($row['contract_end_date']) ? \Carbon\Carbon::parse($row['contract_end_date']) : null,
            'base_salary' => $row['base_salary'],
            'bank_name' => $row['bank_name'] ?? null,
            'account_number' => $row['account_number'] ?? null,
            'nssf_number' => $row['nssf_number'] ?? null,
            'tin_number' => $row['tin_number'] ?? null,
            'nhif_number' => $row['nhif_number'] ?? null,
            'password' => Hash::make($row['employee_id']), // Default password
            'status' => 'active',
        ]);
    }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|unique:employees,employee_id',
            'name' => 'required',
            'email' => 'required|email|unique:employees,email',
            'department' => 'required',
            'position' => 'required',
            'employment_type' => 'required|in:full-time,part-time,contract',
            'hire_date' => 'required|date',
            'base_salary' => 'required|numeric',
            'role' => 'required|exists:roles,name',
        ];
    }
}