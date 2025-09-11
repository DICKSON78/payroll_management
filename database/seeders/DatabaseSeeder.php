<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\Bank;
use App\Models\ComplianceType;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Payslip;
use App\Models\Transaction;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // ===== Create Roles =====
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin'],
            ['name' => 'HR Manager', 'slug' => 'hr'],
            ['name' => 'Employee', 'slug' => 'employee'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $adminRole = Role::where('slug', 'admin')->first();
        $hrRole = Role::where('slug', 'hr')->first();
        $employeeRole = Role::where('slug', 'employee')->first();

        // ===== Create Admin User =====
        $admin = User::firstOrCreate(
            ['email' => 'admin@payroll.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role_id' => $adminRole->id,
            ]
        );

        // ===== Seed Banks =====
        $tanzanianBanks = [
            'NMB Bank',
            'CRDB Bank',
            'Exim Bank',
            'Stanbic Bank',
            'Standard Chartered Bank',
            'Tanzania Commercial Bank (TCB)',
            'Azania Bank',
            'Equity Bank',
            'KCB Bank',
            'Amana Bank',
            'Bank of Africa Tanzania',
            'NBC Bank',
            'PBZ Bank',
            'I&M Bank (Tanzania)',
            'DTB Bank',
            'NCBA Bank',
        ];

        foreach ($tanzanianBanks as $bankName) {
            Bank::firstOrCreate(['name' => $bankName]);
        }

        // ===== Seed Departments =====
        $departments = [
            'HR',
            'Finance',
            'IT',
            'Operations',
            'Marketing',
            'Sales',
            'Customer Service'
        ];

        foreach ($departments as $departmentName) {
            Department::firstOrCreate(['name' => $departmentName]);
        }

        // ===== Seed Compliance Types =====
        $complianceTypes = [
            ['name' => 'NSSF', 'description' => 'National Social Security Fund'],
            ['name' => 'NHIF', 'description' => 'National Health Insurance Fund'],
            ['name' => 'PAYE', 'description' => 'Pay As You Earn Tax'],
        ];

        foreach ($complianceTypes as $complianceType) {
            ComplianceType::firstOrCreate(['name' => $complianceType['name']], $complianceType);
        }

        // ===== Seed Leave Types =====
        $leaveTypes = [
            'Annual Leave',
            'Sick Leave',
            'Maternity Leave',
            'Paternity Leave',
            'Compassionate Leave',
            'Unpaid Leave',
            'Study Leave'
        ];
        $leaveTypeModels = [];
        foreach ($leaveTypes as $type) {
            $leaveTypeModels[] = LeaveType::firstOrCreate(['name' => $type]);
        }

        // ===== Seed Employees and Users =====
        $bankNames = Bank::pluck('name')->toArray();
        $seededEmployees = [];

        for ($i = 1; $i <= 10; $i++) {
            $employeeEmail = 'employee' . $i . '@payroll.com';

            // Create the employee record first
            $employee = Employee::firstOrCreate(
                ['email' => $employeeEmail],
                [
                    'employee_id' => 'SN' . $faker->unique()->numberBetween(1000, 9999),
                    'name' => $faker->name,
                    'email' => $employeeEmail,
                    'department' => $faker->randomElement($departments),
                    'position' => $faker->jobTitle,
                    'status' => $faker->randomElement(['Active', 'Inactive']),
                    'gender' => $faker->randomElement(['Male', 'Female']),
                    'dob' => $faker->date('Y-m-d', '2000-01-01'),
                    'nationality' => $faker->country,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'hire_date' => $faker->date('Y-m-d', 'now'),
                    'base_salary' => $faker->numberBetween(2000, 7000),
                    'allowances' => $faker->numberBetween(100, 1000),
                    'deductions' => $faker->numberBetween(0, 500),
                    'bank_name' => $faker->randomElement($bankNames),
                    'account_number' => $faker->bankAccountNumber,
                    'employment_type' => $faker->randomElement(['Full-Time', 'Part-Time']),
                    'nssf_number' => $faker->unique()->numerify('NSSF#######'),
                    'nhif_number' => $faker->unique()->numerify('NHIF#######'),
                    'tin_number' => $faker->unique()->numerify('TIN#########'),
                ]
            );

            // Create a corresponding user account with a default password
            User::firstOrCreate(
                ['email' => $employee->email],
                [
                    'name' => $employee->name,
                    'password' => Hash::make('password'),
                    'role_id' => $employeeRole->id,
                ]
            );

            $seededEmployees[] = $employee->id;
        }

        // ===== Seed Attendances =====
        $employeeIds = Employee::pluck('id')->toArray();
        $startDate = Carbon::now()->subMonths(3);

        if (!empty($employeeIds)) {
            for ($i = 0; $i < 50; $i++) {
                Attendance::create([
                    'employee_id' => $faker->randomElement($employeeIds),
                    'date' => $faker->dateTimeBetween($startDate, 'now'),
                    'hours_worked' => $faker->randomFloat(2, 6, 12),
                    'overtime_hours' => $faker->randomFloat(2, 0, 4),
                    'status' => $faker->randomElement(['Pending', 'Processed']),
                ]);
            }
        }
        
        // ===== Seed Leave Requests =====
        if (!empty($employeeIds)) {
            $leaveTypeIds = LeaveType::pluck('id')->toArray();
            for ($i = 0; $i < 15; $i++) {
                $startDate = $faker->dateTimeBetween('-1 year', 'now');
                $endDate = $faker->dateTimeBetween($startDate, $startDate->format('Y-m-d') . ' +15 days');
                LeaveRequest::create([
                    'employee_id' => $faker->randomElement($employeeIds),
                    'leave_type_id' => $faker->randomElement($leaveTypeIds),
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'reason' => $faker->sentence,
                    'status' => $faker->randomElement(['Pending', 'Approved', 'Rejected']),
                    'feedback' => $faker->boolean(50) ? $faker->sentence : null,
                ]);
            }
        }

        // ===== Seed Payrolls, Payslips, Transactions, and Reports =====
        $employees = Employee::all();
        $now = Carbon::now();
        $currentMonth = $now->format('Y-m');

        // Valid report types from ReportController
        $validReportTypes = [
            'payslip',
            'payroll_summary',
            'tax_report',
            'nssf_report',
            'nhif_report',
            'wcf_report',
            'sdl_report',
            'year_end_summary',
        ];

        foreach ($employees as $employee) {
            $baseSalary = $employee->base_salary;
            $overtimeHours = $faker->randomFloat(2, 0, 10);
            $overtimePay = $overtimeHours * ($baseSalary / 200); // Simple overtime calculation
            
            // Allowances
            $housingAllowance = $faker->randomFloat(2, 100, 500);
            $transportAllowance = $faker->randomFloat(2, 50, 200);
            $medicalAllowance = $faker->randomFloat(2, 50, 150);
            $totalAllowances = $housingAllowance + $transportAllowance + $medicalAllowance + $employee->allowances;

            $grossSalary = $baseSalary + $overtimePay + $totalAllowances;

            // Deductions
            $nssf = $grossSalary * 0.1; // Example NSSF calculation
            $nhif = $grossSalary * 0.05; // Example NHIF calculation
            $paye = $grossSalary * 0.15; // Example PAYE calculation
            $wcf = $grossSalary * 0.005; // Example WCF calculation
            $sdl = $grossSalary * 0.045; // Example SDL calculation
            $totalDeductions = $nssf + $nhif + $paye + $wcf + $sdl + $employee->deductions;
            
            $netSalary = $grossSalary - $totalDeductions;

            // Create Payroll
            $payroll = Payroll::create([
                'payroll_id' => 'PAY' . $faker->unique()->numerify('######'),
                'employee_id' => $employee->id,
                'period' => $currentMonth,
                'base_salary' => $baseSalary,
                'gross_salary' => $grossSalary,
                'overtime_hours' => $overtimeHours,
                'overtime_pay' => $overtimePay,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'medical_allowance' => $medicalAllowance,
                'adjustment_amount' => $employee->allowances,
                'total_allowances' => $totalAllowances,
                'nssf' => $nssf,
                'paye' => $paye,
                'nhif' => $nhif,
                'wcf' => $wcf,
                'sdl' => $sdl,
                'total_deductions' => $totalDeductions,
                'total_amount' => $netSalary,
                'status' => 'Processed',
            ]);

            // Create Payslip
            $payslip = Payslip::create([
                'payslip_id' => 'PS' . $faker->unique()->numerify('#######'),
                'employee_id' => $employee->id,
                'period' => $currentMonth,
                'base_salary' => $baseSalary,
                'gross_salary' => $grossSalary,
                'overtime_hours' => $overtimeHours,
                'overtime_pay' => $overtimePay,
                'housing_allowance' => $housingAllowance,
                'transport_allowance' => $transportAllowance,
                'medical_allowance' => $medicalAllowance,
                'adjustment_amount' => $employee->allowances,
                'total_allowances' => $totalAllowances,
                'nssf' => $nssf,
                'paye' => $paye,
                'nhif' => $nhif,
                'wcf' => $wcf,
                'sdl' => $sdl,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'status' => 'Generated',
            ]);

            // Create Transaction
            Transaction::create([
                'transaction_id' => 'TRN' . $faker->unique()->numerify('########'),
                'employee_id' => $employee->id,
                'payslip_id' => $payslip->id,
                'amount' => $netSalary,
                'status' => 'Completed',
            ]);

            // Create Report
            Report::create([
                'report_id' => 'REP' . $faker->unique()->numerify('######'),
                'type' => $faker->randomElement($validReportTypes),
                'period' => $currentMonth,
                'employee_id' => $employee->id,
                'export_format' => $faker->randomElement(['pdf', 'excel']),
                'generated_by' => $admin->id,
                'status' => 'completed',
            ]);
        }
    }
}