<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeesImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        $defaultPassword = Hash::make('password123');
        $employeesToCreate = [];

        foreach ($rows as $row) {
            // Validate each row
            $validator = Validator::make($row->toArray(), [
                'name' => 'required|string|max:255',
                'employee_id' => 'required|string|max:50|unique:employees',
                'email' => 'required|email|max:255|unique:users',
                'department' => 'required|string|max:100',
                'position' => 'required|string|max:100',
                'role' => 'required|string|in:admin,hr,manager,employee',
                'base_salary' => 'required|numeric|min:0',
                'allowances' => 'nullable|numeric|min:0',
                'bank_name' => 'nullable|string|max:100',
                'account_number' => 'nullable|string|max:50',
                'hire_date' => 'required|date',
            ]);

            if ($validator->fails()) {
                // Skip invalid rows and log the error for debugging
                continue;
            }

            // Prepare the data for creating user and employee records
            $employeesToCreate[] = [
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => $defaultPassword,
                'role' => $row['role'],
                'employee_data' => $row->except(['name', 'email', 'password', 'role'])->toArray(),
            ];
        }

        // Use a database transaction for a bulk operation
        DB::transaction(function () use ($employeesToCreate) {
            foreach ($employeesToCreate as $employeeData) {
                // Create user first
                $user = User::create([
                    'name' => $employeeData['name'],
                    'email' => $employeeData['email'],
                    'password' => $employeeData['password'],
                    'role' => $employeeData['role'],
                ]);

                // Create the employee and link to the new user
                $employee = new Employee($employeeData['employee_data']);
                $employee->user_id = $user->id;
                $employee->save();
            }
        });
    }
}
