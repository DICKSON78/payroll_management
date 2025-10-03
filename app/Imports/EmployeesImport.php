<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Role;
use App\Models\Bank;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        Log::info('Starting bulk import with ' . $rows->count() . ' rows');
        
        foreach ($rows as $index => $row) {
            try {
                Log::info('Processing row ' . ($index + 1) . ': ' . json_encode($row->toArray()));
                
                // Validate required fields
                if (empty($row['name']) || empty($row['email']) || empty($row['department']) || 
                    empty($row['position']) || empty($row['employment_type']) || empty($row['hire_date']) || 
                    empty($row['base_salary']) || empty($row['role'])) {
                    Log::error('Missing required fields in row ' . ($index + 1));
                    continue;
                }

                // Check if email already exists
                if (Employee::where('email', $row['email'])->exists()) {
                    Log::error('Email already exists: ' . $row['email']);
                    continue;
                }

                // Validate department exists
                if (!Department::where('name', $row['department'])->exists()) {
                    Log::error('Department does not exist: ' . $row['department']);
                    continue;
                }

                // Validate role exists
                if (!Role::where('slug', $row['role'])->exists()) {
                    Log::error('Role does not exist: ' . $row['role']);
                    continue;
                }

                // Generate unique employee ID
                $employeeId = $this->generateUniqueEmployeeId();

                // Prepare employee data
                $employeeData = [
                    'employee_id' => $employeeId,
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make('password123'), // Default password
                    'department' => $row['department'],
                    'position' => $row['position'],
                    'role' => $row['role'],
                    'employment_type' => $row['employment_type'],
                    'hire_date' => Carbon::parse($row['hire_date']),
                    'base_salary' => $row['base_salary'],
                    'status' => 'active',
                    'allowances' => 0.00,
                    'deductions' => 0.00,
                ];

                // Add optional fields
                $optionalFields = [
                    'phone', 'gender', 'dob', 'nationality', 'address',
                    'bank_name', 'account_number', 'nssf_number', 'tin_number', 'nhif_number'
                ];

                foreach ($optionalFields as $field) {
                    if (isset($row[$field]) && !empty($row[$field])) {
                        if ($field === 'dob' || $field === 'contract_end_date') {
                            $employeeData[$field] = Carbon::parse($row[$field]);
                        } else {
                            $employeeData[$field] = $row[$field];
                        }
                    }
                }

                // Handle contract end date for contract employees
                if ($row['employment_type'] === 'contract' && !empty($row['contract_end_date'])) {
                    $employeeData['contract_end_date'] = Carbon::parse($row['contract_end_date']);
                }

                Log::info('Creating employee with data: ' . json_encode($employeeData));
                
                // Create employee
                Employee::create($employeeData);
                
                Log::info('Successfully created employee: ' . $employeeId);

            } catch (\Exception $e) {
                Log::error('Error importing row ' . ($index + 1) . ': ' . $e->getMessage());
                continue;
            }
        }
        
        Log::info('Bulk import completed');
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