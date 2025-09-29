<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        $this->truncateTables();

        // Seed roles
        $this->seedRoles();

        // Seed departments
        $this->seedDepartments();

        // Seed banks
        $this->seedBanks();

        // Seed settings
        $this->seedSettings();

        // Seed allowances
        $this->seedAllowances();

        // Seed deductions
        $this->seedDeductions();

        // Seed employees (10 employees)
        $this->seedEmployees();

        // Seed sample attendances
        $this->seedAttendances();

        // Seed sample leave requests
        $this->seedLeaveRequests();

        // Seed sample payrolls
        $this->seedPayrolls();

        // Seed sample payslips
        $this->seedPayslips();

        // Seed sample compliance tasks
        $this->seedComplianceTasks();

        // Seed sample reports
        $this->seedReports();

        // Seed sample transactions
        $this->seedTransactions();

        // Seed sample payroll alerts
        $this->seedPayrollAlerts();

        $this->command->info('Database seeded successfully with 10 employees!');
    }

    private function truncateTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        $tables = [
            'roles',
            'departments',
            'banks',
            'employees',
            'settings',
            'allowances',
            'deductions',
            'attendances',
            'leave_requests',
            'payrolls',
            'payslips',
            'compliance_tasks',
            'reports',
            'transactions',
            'payroll_alerts',
            'password_reset_tokens',
            'sessions',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function seedRoles()
    {
        DB::table('roles')->insert([
            ['name' => 'admin', 'slug' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'hr manager', 'slug' => 'hr', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'employee', 'slug' => 'employee', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'manager', 'slug' => 'manager', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedDepartments()
    {
        DB::table('departments')->insert([
            ['name' => 'Operations', 'description' => 'Operations Department', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HR', 'description' => 'Human Resources Department', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance', 'description' => 'Finance Department', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'IT', 'description' => 'Information Technology Department', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Marketing', 'description' => 'Marketing Department', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedBanks()
    {
        DB::table('banks')->insert([
            ['name' => 'CRDB Bank', 'code' => 'CRDB', 'swift_code' => 'CORUTZTZ', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NMB Bank', 'code' => 'NMB', 'swift_code' => 'NMBTZTXZ', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NBC Bank', 'code' => 'NBC', 'swift_code' => 'NLCBTZTX', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Stanbic Bank', 'code' => 'STANBIC', 'swift_code' => 'SBICTZTX', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Exim Bank', 'code' => 'EXIM', 'swift_code' => 'EXIMTZTX', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedSettings()
    {
        DB::table('settings')->insert([
            [
                'key' => 'pay_schedule', 'value' => 'monthly', 'type' => 'string', 'category' => 'payroll',
                'description' => 'Payroll processing schedule', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'processing_day', 'value' => '25', 'type' => 'integer', 'category' => 'payroll',
                'description' => 'Day of month for payroll processing', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'default_currency', 'value' => 'TZS', 'type' => 'string', 'category' => 'payroll',
                'description' => 'Default currency for payroll', 'is_public' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'overtime_calculation', 'value' => '1.5x', 'type' => 'string', 'category' => 'payroll',
                'description' => 'Overtime rate calculation method', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'nssf_employer_rate', 'value' => '10.0', 'type' => 'decimal', 'category' => 'payroll',
                'description' => 'NSSF employer contribution rate (%)', 'is_public' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'nssf_employee_rate', 'value' => '10.0', 'type' => 'decimal', 'category' => 'payroll',
                'description' => 'NSSF employee contribution rate (%)', 'is_public' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'nhif_calculation_method', 'value' => 'tiered', 'type' => 'string', 'category' => 'payroll',
                'description' => 'NHIF contribution calculation method', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'paye_tax_free', 'value' => '270000', 'type' => 'integer', 'category' => 'payroll',
                'description' => 'PAYE tax-free threshold (TZS)', 'is_public' => 1, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'wcf_rate', 'value' => '0.5', 'type' => 'decimal', 'category' => 'payroll',
                'description' => 'Workers Compensation Fund rate (%)', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'sdl_rate', 'value' => '3.5', 'type' => 'decimal', 'category' => 'payroll',
                'description' => 'Skills Development Levy rate (%)', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'email_notifications', 'value' => '["payroll_processing","payment_confirmation"]', 'type' => 'array', 'category' => 'notifications',
                'description' => 'Enabled email notification types', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'sms_enabled', 'value' => 'false', 'type' => 'boolean', 'category' => 'notifications',
                'description' => 'Enable SMS notifications', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'sms_gateway', 'value' => 'twilio', 'type' => 'string', 'category' => 'notifications',
                'description' => 'SMS gateway provider', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'accounting_software', 'value' => '', 'type' => 'string', 'category' => 'integrations',
                'description' => 'Integrated accounting software', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
            [
                'key' => 'attendance_sync', 'value' => 'false', 'type' => 'boolean', 'category' => 'integrations',
                'description' => 'Enable attendance data sync', 'is_public' => 0, 'created_at' => now(), 'updated_at' => now()
            ],
        ]);
    }

    private function seedAllowances()
    {
        DB::table('allowances')->insert([
            ['name' => 'House Allowance', 'type' => 'fixed', 'amount' => 200.00, 'taxable' => 0, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transport Allowance', 'type' => 'fixed', 'amount' => 150.00, 'taxable' => 0, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Medical Allowance', 'type' => 'fixed', 'amount' => 100.00, 'taxable' => 1, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Overtime Bonus', 'type' => 'percentage', 'amount' => 10.00, 'taxable' => 1, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedDeductions()
    {
        DB::table('deductions')->insert([
            ['name' => 'NSSF', 'category' => 'statutory', 'type' => 'percentage', 'amount' => 10.00, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NHIF', 'category' => 'statutory', 'type' => 'fixed', 'amount' => 300.00, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'PAYE Tax', 'category' => 'statutory', 'type' => 'percentage', 'amount' => 15.00, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Loan Repayment', 'category' => 'voluntary', 'type' => 'fixed', 'amount' => 200.00, 'active' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    private function seedEmployees()
    {
        // Define valid role slugs from roles table
        $validRoles = DB::table('roles')->pluck('slug')->toArray();

        // Define employee data
        $employeeData = [
            // Admin
            [
                'name' => 'Admin User', 'email' => 'admin@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Operations', 'role' => 'admin',
                'position' => 'System Administrator', 'base_salary' => 9000.00, 'status' => 'active',
                'gender' => 'male', 'dob' => '1985-05-15', 'nationality' => 'Tanzanian',
                'phone' => '+255712345678', 'address' => 'Dar es Salaam', 'hire_date' => '2024-01-15',
                'bank_name' => 'CRDB Bank', 'account_number' => '1234567890', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF001', 'nhif_number' => 'NHIF001', 'tin_number' => 'TIN001',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // HR Manager
            [
                'name' => 'HR Manager Jane', 'email' => 'hr@payroll.com',
                'password' => Hash::make('password'), 'department' => 'HR', 'role' => 'hr',
                'position' => 'HR Manager', 'base_salary' => 7500.00, 'status' => 'active',
                'gender' => 'female', 'dob' => '1990-08-20', 'nationality' => 'Tanzanian',
                'phone' => '+255712345679', 'address' => 'Dar es Salaam', 'hire_date' => '2024-02-01',
                'bank_name' => 'NMB Bank', 'account_number' => '1234567891', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF002', 'nhif_number' => 'NHIF002', 'tin_number' => 'TIN002',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Operations Manager
            [
                'name' => 'Operations Manager John', 'email' => 'ops@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Operations', 'role' => 'manager',
                'position' => 'Operations Manager', 'base_salary' => 8000.00, 'status' => 'active',
                'gender' => 'male', 'dob' => '1988-03-10', 'nationality' => 'Tanzanian',
                'phone' => '+255712345680', 'address' => 'Dar es Salaam', 'hire_date' => '2024-02-15',
                'bank_name' => 'NBC Bank', 'account_number' => '1234567892', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF003', 'nhif_number' => 'NHIF003', 'tin_number' => 'TIN003',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // IT Manager
            [
                'name' => 'IT Manager David', 'email' => 'it@payroll.com',
                'password' => Hash::make('password'), 'department' => 'IT', 'role' => 'manager',
                'position' => 'IT Manager', 'base_salary' => 8500.00, 'status' => 'active',
                'gender' => 'male', 'dob' => '1987-11-25', 'nationality' => 'Tanzanian',
                'phone' => '+255712345681', 'address' => 'Dar es Salaam', 'hire_date' => '2024-03-01',
                'bank_name' => 'Stanbic Bank', 'account_number' => '1234567893', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF004', 'nhif_number' => 'NHIF004', 'tin_number' => 'TIN004',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Finance Manager
            [
                'name' => 'Finance Manager Mary', 'email' => 'finance@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Finance', 'role' => 'manager',
                'position' => 'Finance Manager', 'base_salary' => 8200.00, 'status' => 'active',
                'gender' => 'female', 'dob' => '1989-07-12', 'nationality' => 'Tanzanian',
                'phone' => '+255712345682', 'address' => 'Dar es Salaam', 'hire_date' => '2024-03-15',
                'bank_name' => 'Exim Bank', 'account_number' => '1234567894', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF005', 'nhif_number' => 'NHIF005', 'tin_number' => 'TIN005',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Senior Developer
            [
                'name' => 'Senior Developer Alice', 'email' => 'alice@payroll.com',
                'password' => Hash::make('password'), 'department' => 'IT', 'role' => 'employee',
                'position' => 'Senior Software Developer', 'base_salary' => 6000.00, 'status' => 'active',
                'gender' => 'female', 'dob' => '1992-04-18', 'nationality' => 'Tanzanian',
                'phone' => '+255712345683', 'address' => 'Dar es Salaam', 'hire_date' => '2024-04-01',
                'bank_name' => 'CRDB Bank', 'account_number' => '1234567895', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF006', 'nhif_number' => 'NHIF006', 'tin_number' => 'TIN006',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Accountant
            [
                'name' => 'Accountant Bob', 'email' => 'bob@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Finance', 'role' => 'employee',
                'position' => 'Senior Accountant', 'base_salary' => 5500.00, 'status' => 'active',
                'gender' => 'male', 'dob' => '1991-09-30', 'nationality' => 'Tanzanian',
                'phone' => '+255712345684', 'address' => 'Dar es Salaam', 'hire_date' => '2024-04-15',
                'bank_name' => 'NMB Bank', 'account_number' => '1234567896', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF007', 'nhif_number' => 'NHIF007', 'tin_number' => 'TIN007',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // HR Assistant
            [
                'name' => 'HR Assistant Clara', 'email' => 'clara@payroll.com',
                'password' => Hash::make('password'), 'department' => 'HR', 'role' => 'employee',
                'position' => 'HR Assistant', 'base_salary' => 4500.00, 'status' => 'active',
                'gender' => 'female', 'dob' => '1993-12-05', 'nationality' => 'Tanzanian',
                'phone' => '+255712345685', 'address' => 'Dar es Salaam', 'hire_date' => '2024-05-01',
                'bank_name' => 'NBC Bank', 'account_number' => '1234567897', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF008', 'nhif_number' => 'NHIF008', 'tin_number' => 'TIN008',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Marketing Specialist
            [
                'name' => 'Marketing Specialist Tom', 'email' => 'tom@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Marketing', 'role' => 'employee',
                'position' => 'Marketing Specialist', 'base_salary' => 5000.00, 'status' => 'active',
                'gender' => 'male', 'dob' => '1994-06-22', 'nationality' => 'Tanzanian',
                'phone' => '+255712345686', 'address' => 'Dar es Salaam', 'hire_date' => '2024-05-15',
                'bank_name' => 'Stanbic Bank', 'account_number' => '1234567898', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF009', 'nhif_number' => 'NHIF009', 'tin_number' => 'TIN009',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
            // Operations Staff
            [
                'name' => 'Operations Staff Sarah', 'email' => 'sarah@payroll.com',
                'password' => Hash::make('password'), 'department' => 'Operations', 'role' => 'employee',
                'position' => 'Operations Staff', 'base_salary' => 4000.00, 'status' => 'active',
                'gender' => 'female', 'dob' => '1995-01-14', 'nationality' => 'Tanzanian',
                'phone' => '+255712345687', 'address' => 'Dar es Salaam', 'hire_date' => '2024-06-01',
                'bank_name' => 'Exim Bank', 'account_number' => '1234567899', 'employment_type' => 'full-time',
                'nssf_number' => 'NSSF010', 'nhif_number' => 'NHIF010', 'tin_number' => 'TIN010',
                'allowances' => 350.00, 'deductions' => 650.00, 'created_at' => now(), 'updated_at' => now()
            ],
        ];

        $employees = [];
        foreach ($employeeData as $data) {
            // Generate unique employee_id in EMP-XXXXXXXX format
            do {
                $employeeId = 'EMP-' . Str::random(8);
            } while (DB::table('employees')->where('employee_id', $employeeId)->exists());

            // Validate role
            if (!in_array($data['role'], $validRoles)) {
                $this->command->error('Invalid role slug: ' . $data['role']);
                continue;
            }

            // Prepare employee record
            $employees[] = [
                'employee_id' => $employeeId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'department' => $data['department'],
                'role' => $data['role'],
                'position' => $data['position'],
                'base_salary' => $data['base_salary'],
                'status' => $data['status'],
                'gender' => $data['gender'],
                'dob' => $data['dob'],
                'nationality' => $data['nationality'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'hire_date' => $data['hire_date'],
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'employment_type' => $data['employment_type'],
                'nssf_number' => $data['nssf_number'],
                'nhif_number' => $data['nhif_number'],
                'tin_number' => $data['tin_number'],
                'allowances' => $data['allowances'],
                'deductions' => $data['deductions'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
            ];
        }

        DB::table('employees')->insert($employees);
    }

    private function seedAttendances()
    {
        $attendances = [];
        $employees = DB::table('employees')->get();
        
        foreach ($employees as $employee) {
            for ($day = 1; $day <= 20; $day++) {
                $date = now()->subDays(20 - $day)->format('Y-m-d');
                $checkIn = Carbon::createFromTime(8, rand(0, 30), 0);
                $checkOut = $checkIn->copy()->addHours(9);
                $hoursWorked = 9.00;
                
                $attendances[] = [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'date' => $date,
                    'check_in' => $checkIn->format('H:i:s'),
                    'check_out' => $checkOut->format('H:i:s'),
                    'status' => 'Present',
                    'hours_worked' => $hoursWorked,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert in chunks to avoid memory issues
        foreach (array_chunk($attendances, 100) as $chunk) {
            DB::table('attendances')->insert($chunk);
        }
    }

    private function seedLeaveRequests()
    {
        $employees = DB::table('employees')->get()->keyBy('email');
        DB::table('leave_requests')->insert([
            [
                'request_id' => 'LRQ001', 
                'employee_id' => $employees['alice@payroll.com']->id, 
                'employee_name' => 'Senior Developer Alice',
                'leave_type' => 'Annual', 
                'start_date' => now()->addDays(10)->format('Y-m-d'),
                'end_date' => now()->addDays(17)->format('Y-m-d'), 
                'days' => 7,
                'reason' => 'Annual vacation leave', 
                'status' => 'Pending',
                'approved_by' => null,
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'request_id' => 'LRQ002', 
                'employee_id' => $employees['bob@payroll.com']->id, 
                'employee_name' => 'Accountant Bob',
                'leave_type' => 'Sick', 
                'start_date' => now()->subDays(2)->format('Y-m-d'),
                'end_date' => now()->addDays(3)->format('Y-m-d'), 
                'days' => 5,
                'reason' => 'Medical treatment', 
                'status' => 'Approved', 
                'approved_by' => $employees['hr@payroll.com']->id,
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
    }

    private function seedPayrolls()
    {
        $payrolls = [];
        $employees = DB::table('employees')->get();
        $period = now()->format('Y-m');
        
        foreach ($employees as $employee) {
            $payrolls[] = [
                'payroll_id' => 'PAY' . $period . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'period' => $period,
                'base_salary' => $employee->base_salary,
                'allowances' => $employee->allowances,
                'total_amount' => $employee->base_salary + $employee->allowances,
                'deductions' => $employee->deductions,
                'net_salary' => ($employee->base_salary + $employee->allowances) - $employee->deductions,
                'status' => 'Processed',
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'Bank Transfer',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payrolls')->insert($payrolls);
    }

    private function seedPayslips()
    {
        $payslips = [];
        $employees = DB::table('employees')->get();
        $period = now()->format('Y-m');
        
        foreach ($employees as $employee) {
            $payroll = DB::table('payrolls')->where('employee_id', $employee->id)->first();
            
            $payslips[] = [
                'payslip_id' => 'PSLIP' . $period . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'period' => $period,
                'base_salary' => $payroll->base_salary,
                'allowances' => $payroll->allowances,
                'deductions' => $payroll->deductions,
                'net_salary' => $payroll->net_salary,
                'status' => 'Generated',
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('payslips')->insert($payslips);
    }

    private function seedComplianceTasks()
    {
        $employees = DB::table('employees')->get()->keyBy('email');
        DB::table('compliance_tasks')->insert([
            [
                'task_id' => 'CTASK001', 
                'type' => 'nssf_submission', 
                'description' => 'Monthly NSSF submission',
                'due_date' => now()->addDays(5)->format('Y-m-d'), 
                'employee_id' => $employees['hr@payroll.com']->id,
                'status' => 'Pending', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'task_id' => 'CTASK002', 
                'type' => 'tax_filing', 
                'description' => 'Monthly PAYE tax filing',
                'due_date' => now()->addDays(7)->format('Y-m-d'), 
                'employee_id' => $employees['finance@payroll.com']->id,
                'status' => 'Pending', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
    }

    private function seedReports()
    {
        $employees = DB::table('employees')->get()->keyBy('email');
        DB::table('reports')->insert([
            [
                'report_id' => 'RPT001', 
                'type' => 'payroll_summary', 
                'period' => now()->format('Y-m'),
                'employee_id' => null,
                'batch_number' => 1,
                'export_format' => 'pdf', 
                'generated_by' => $employees['admin@payroll.com']->id, 
                'status' => 'completed',
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'report_id' => 'RPT002', 
                'type' => 'tax_report', 
                'period' => now()->format('Y-m'),
                'employee_id' => null,
                'batch_number' => 2,
                'export_format' => 'excel', 
                'generated_by' => $employees['finance@payroll.com']->id, 
                'status' => 'pending',
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
    }

    private function seedTransactions()
    {
        $transactions = [];
        $employees = DB::table('employees')->get();
        
        foreach ($employees as $employee) {
            $payroll = DB::table('payrolls')->where('employee_id', $employee->id)->first();
            
            $transactions[] = [
                'transaction_id' => 'TXN' . now()->format('Ymd') . str_pad($employee->id, 3, '0', STR_PAD_LEFT),
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'type' => 'salary_payment',
                'amount' => $payroll->net_salary,
                'transaction_date' => now()->format('Y-m-d'),
                'status' => 'Completed',
                'payment_method' => 'Bank Transfer',
                'description' => 'Salary payment for ' . $payroll->period,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('transactions')->insert($transactions);
    }

    private function seedPayrollAlerts()
    {
        $employees = DB::table('employees')->get()->keyBy('email');
        DB::table('payroll_alerts')->insert([
            [
                'alert_id' => 'ALT001', 
                'employee_id' => $employees['admin@payroll.com']->id, 
                'type' => 'payroll_processed',
                'message' => 'Payroll for ' . now()->format('F Y') . ' has been processed successfully',
                'status' => 'Unread', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'alert_id' => 'ALT002', 
                'employee_id' => $employees['hr@payroll.com']->id, 
                'type' => 'compliance_due',
                'message' => 'NSSF submission is due in 5 days',
                'status' => 'Unread', 
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);
    }
}