<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Allowance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            try {
                // Skip empty rows
                if (empty($row['name']) || empty($row['email'])) {
                    continue;
                }

                // Check if employee already exists
                $existingEmployee = Employee::where('email', $row['email'])->first();
                if ($existingEmployee) {
                    Log::warning("Employee with email {$row['email']} already exists. Skipping.");
                    continue;
                }

                // Generate unique employee ID
                $employeeId = $this->generateUniqueEmployeeId();

                // Prepare employee data
                $employeeData = [
                    'employee_id' => $employeeId,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make(strtolower(explode(' ', $row['name'])[0] ?? 'employee123')),
                    'department' => $row['department'],
                    'position' => $row['position'],
                    'role' => $row['role'],
                    'base_salary' => $row['base_salary'],
                    'employment_type' => $row['employment_type'],
                    'hire_date' => $row['hire_date'],
                    'status' => 'active',
                    'allowances' => 0.00,
                    'deductions' => 0.00,
                ];

                // Add optional fields
                $optionalFields = [
                    'phone', 'gender', 'dob', 'nationality', 'address', 
                    'contract_end_date', 'bank_name', 'account_number',
                    'nssf_number', 'tin_number', 'nhif_number'
                ];

                foreach ($optionalFields as $field) {
                    if (isset($row[$field]) && !empty($row[$field])) {
                        $employeeData[$field] = $row[$field];
                    }
                }

                // Create employee
                $employee = Employee::create($employeeData);

                Log::info("Successfully imported employee: {$employee->employee_id}");

            } catch (\Exception $e) {
                Log::error("Failed to import employee row: " . $e->getMessage());
                continue;
            }
        }
    }

    private function generateUniqueEmployeeId()
    {
        $prefix = "EMP";
        do {
            $randomPart = strtoupper(Str::random(8));
            $newId = $prefix . '-' . $randomPart;
        } while (Employee::where('employee_id', $newId)->exists());

        return $newId;
    }
}