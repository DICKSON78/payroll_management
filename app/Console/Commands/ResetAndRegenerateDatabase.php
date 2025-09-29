<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ResetAndRegenerateDatabase extends Command
{
    protected $signature = 'db:reset-regenerate';
    protected $description = 'Delete all migrations and models, then regenerate database schema and models';

    public function handle()
    {
        $this->info('Starting database reset and regeneration...');

        // Step 1: Backup database
        $this->info('Please ensure you have backed up your database before proceeding.');
        if (!$this->confirm('Have you backed up your database?')) {
            $this->error('Aborting. Please back up your database and try again.');
            return;
        }

        // Step 2: Delete existing migrations
        $migrationPath = database_path('migrations');
        File::cleanDirectory($migrationPath);
        $this->info('Deleted all migration files.');

        // Step 3: Delete existing models
        $modelPath = app_path('Models');
        File::cleanDirectory($modelPath);
        $this->info('Deleted all model files.');

        // Step 4: Reset migrations table
        Schema::dropIfExists('migrations');
        $this->info('Dropped migrations table.');

        // Step 5: Create new migrations
        $this->createMigrations();
        $this->info('Created new migration files.');

        // Step 6: Run migrations
        Artisan::call('migrate');
        $this->info('Ran migrations successfully.');

        // Step 7: Create new models
        $this->createModels();
        $this->info('Created new model files.');

        // Step 8: Update auth configuration
        $this->updateAuthConfig();
        $this->info('Updated authentication configuration.');

        // Step 9: Seed database
        $this->seedDatabase();
        $this->info('Seeded database with initial data (7 employees and related tables).');

        $this->info('Database reset and regeneration completed successfully!');
    }

    protected function createMigrations()
    {
        $migrations = [
            '2025_09_22_000001_create_roles_table' => $this->getRolesMigration(),
            '2025_09_22_000002_create_departments_table' => $this->getDepartmentsMigration(),
            '2025_09_22_000003_create_banks_table' => $this->getBanksMigration(),
            '2025_09_22_000004_create_employees_table' => $this->getEmployeesMigration(),
            '2025_09_22_000005_create_attendances_table' => $this->getAttendancesMigration(),
            '2025_09_22_000006_create_compliance_tasks_table' => $this->getComplianceTasksMigration(),
            '2025_09_22_000007_create_leave_requests_table' => $this->getLeaveRequestsMigration(),
            '2025_09_22_000008_create_payrolls_table' => $this->getPayrollsMigration(),
            '2025_09_22_000009_create_payroll_alerts_table' => $this->getPayrollAlertsMigration(),
            '2025_09_22_000010_create_payslips_table' => $this->getPayslipsMigration(),
            '2025_09_22_000011_create_reports_table' => $this->getReportsMigration(),
            '2025_09_22_000012_create_sessions_table' => $this->getSessionsMigration(),
            '2025_09_22_000013_create_transactions_table' => $this->getTransactionsMigration(),
            '2025_09_22_000014_create_password_reset_tokens_table' => $this->getPasswordResetTokensMigration(),
        ];

        foreach ($migrations as $fileName => $content) {
            File::put(database_path("migrations/{$fileName}.php"), $content);
        }
    }

    protected function createModels()
    {
        $models = [
            'Role' => $this->getRoleModel(),
            'Department' => $this->getDepartmentModel(),
            'Bank' => $this->getBankModel(),
            'Employee' => $this->getEmployeeModel(),
            'Attendance' => $this->getAttendanceModel(),
            'ComplianceTask' => $this->getComplianceTaskModel(),
            'LeaveRequest' => $this->getLeaveRequestModel(),
            'Payroll' => $this->getPayrollModel(),
            'PayrollAlert' => $this->getPayrollAlertModel(),
            'Payslip' => $this->getPayslipModel(),
            'Report' => $this->getReportModel(),
            'Session' => $this->getSessionModel(),
            'Transaction' => $this->getTransactionModel(),
            'PasswordResetToken' => $this->getPasswordResetTokenModel(),
        ];

        foreach ($models as $modelName => $content) {
            File::put(app_path("Models/{$modelName}.php"), $content);
        }
    }

    protected function updateAuthConfig()
    {
        $authConfig = <<<PHP
<?php

return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'employees',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'employees',
        ],
    ],

    'providers' => [
        'employees' => [
            'driver' => 'eloquent',
            'model' => App\Models\Employee::class,
        ],
    ],

    'passwords' => [
        'employees' => [
            'provider' => 'employees',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,
];
PHP;
        File::put(config_path('auth.php'), $authConfig);
    }

    protected function seedDatabase()
    {
        // Seed roles
        DB::table('roles')->insert([
            ['name' => 'Admin', 'slug' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HR Manager', 'slug' => 'hr', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Employee', 'slug' => 'employee', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manager', 'slug' => 'manager', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed departments
        DB::table('departments')->insert([
            ['name' => 'Operations', 'description' => 'Operations Department', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'HR', 'description' => 'Human Resources', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Finance', 'description' => 'Finance Department', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed banks (Tanzanian banks)
        DB::table('banks')->insert([
            ['name' => 'CRDB Bank', 'code' => 'CRDB', 'swift_code' => 'CORUTZTZ', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NMB Bank', 'code' => 'NMB', 'swift_code' => 'NMBTZTXZ', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'NBC Bank', 'code' => 'NBC', 'swift_code' => 'NLCBTZTX', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed 7 employees
        $employees = [
            [
                'employee_id' => 'EMP0001',
                'role' => 'Admin',
                'name' => 'Admin User',
                'email' => 'admin@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'Operations',
                'position' => 'System Administrator',
                'base_salary' => 9000.00,
                'hire_date' => '2024-09-17',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'CRDB Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0002',
                'role' => 'HR Manager',
                'name' => 'HR Manager Jane',
                'email' => 'hr@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'HR',
                'position' => 'HR Manager',
                'base_salary' => 7500.00,
                'hire_date' => '2024-09-18',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'NMB Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0003',
                'role' => 'Manager',
                'name' => 'Operations Manager John',
                'email' => 'ops@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'Operations',
                'position' => 'Operations Manager',
                'base_salary' => 8000.00,
                'hire_date' => '2024-09-19',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'NBC Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0004',
                'role' => 'Employee',
                'name' => 'Alice Smith',
                'email' => 'alice@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'Operations',
                'position' => 'Staff',
                'base_salary' => 5000.00,
                'hire_date' => '2024-09-20',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'CRDB Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0005',
                'role' => 'Employee',
                'name' => 'Bob Johnson',
                'email' => 'bob@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'HR',
                'position' => 'HR Assistant',
                'base_salary' => 4500.00,
                'hire_date' => '2024-09-21',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'NMB Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0006',
                'role' => 'Employee',
                'name' => 'Clara Brown',
                'email' => 'clara@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'Finance',
                'position' => 'Accountant',
                'base_salary' => 6000.00,
                'hire_date' => '2024-09-22',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'NBC Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 'EMP0007',
                'role' => 'Employee',
                'name' => 'David Lee',
                'email' => 'david@payroll.com',
                'password' => bcrypt('password'),
                'department' => 'Operations',
                'position' => 'Supervisor',
                'base_salary' => 5500.00,
                'hire_date' => '2024-09-23',
                'employment_type' => 'Full-Time',
                'status' => 'Active',
                'bank_name' => 'CRDB Bank',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('employees')->insert($employees);
    }

    // Migration templates
    protected function getRolesMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    public function up()
    {
        Schema::create('roles', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('name')->comment('Role name (e.g., Admin, HR Manager)');
            \$table->string('slug')->unique()->comment('Unique role identifier');
            \$table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('roles');
    }
}
PHP;
    }

    protected function getDepartmentsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('name')->comment('Department name');
            \$table->text('description')->nullable()->comment('Department description');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}
PHP;
    }

    protected function getBanksMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    public function up()
    {
        Schema::create('banks', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('name')->unique()->comment('Bank name');
            \$table->string('code')->unique()->nullable()->comment('Bank code');
            \$table->string('swift_code')->nullable()->comment('SWIFT/BIC code');
            \$table->string('contact_email')->nullable()->comment('Contact email');
            \$table->string('contact_phone')->nullable()->comment('Contact phone');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('banks');
    }
}
PHP;
    }

    protected function getEmployeesMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint \$table) {
            \$table->id();
            \$table->string('employee_id')->unique();
            \$table->string('name');
            \$table->string('email')->unique()->nullable();
            \$table->string('password')->nullable();
            \$table->string('remember_token', 100)->nullable();
            \$table->timestamp('email_verified_at')->nullable();
            \$table->string('department');
            \$table->string('role');
            \$table->string('position');
            \$table->decimal('base_salary', 15, 2);
            \$table->decimal('allowances', 15, 2)->nullable();
            \$table->decimal('deductions', 15, 2)->default(0.00);
            \$table->string('status')->default('Active');
            \$table->string('gender')->nullable();
            \$table->date('dob')->nullable();
            \$table->string('nationality')->nullable();
            \$table->string('phone')->nullable();
            \$table->string('address')->nullable();
            \$table->date('hire_date');
            \$table->date('contract_end_date')->nullable();
            \$table->string('bank_name')->nullable()->comment('Name of the bank from banks table');
            \$table->string('account_number')->nullable();
            \$table->string('employment_type')->default('Full-Time');
            \$table->string('nssf_number')->nullable();
            \$table->string('nhif_number')->nullable();
            \$table->string('tin_number')->nullable();
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
PHP;
    }

    protected function getAttendancesMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            \$table->string('employee_name');
            \$table->date('date')->comment('Date of attendance');
            \$table->time('check_in')->nullable()->comment('Check-in time');
            \$table->time('check_out')->nullable()->comment('Check-out time');
            \$table->string('status')->default('Present')->comment('Attendance status: Present, Absent, Leave, Holiday');
            \$table->decimal('hours_worked', 5, 2)->nullable()->comment('Total hours worked');
            \$table->text('notes')->nullable()->comment('Additional notes');
            \$table->timestamps();
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
PHP;
    }

    protected function getComplianceTasksMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplianceTasksTable extends Migration
{
    public function up()
    {
        Schema::create('compliance_tasks', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('task_id')->unique()->comment('Unique task identifier (e.g., CTASK-123)');
            \$table->string('type')->comment('Task type: tax_filing, nssf_submission, nhif_submission, wcf_submission, sdl_submission');
            \$table->string('description')->nullable()->comment('Task description');
            \$table->date('due_date')->comment('Due date for the task');
            \$table->unsignedBigInteger('employee_id')->nullable()->comment('Assigned employee');
            \$table->string('status')->default('Pending')->comment('Task status: Pending, Completed, Overdue');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compliance_tasks');
    }
}
PHP;
    }

    protected function getLeaveRequestsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('request_id')->unique()->comment('Unique request identifier (e.g., LRQ-123)');
            \$table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            \$table->string('employee_name');
            \$table->string('leave_type')->comment('Leave type: Annual, Sick, Maternity, Unpaid');
            \$table->date('start_date');
            \$table->date('end_date');
            \$table->integer('days')->comment('Number of leave days');
            \$table->text('reason')->nullable()->comment('Reason for leave');
            \$table->string('status')->default('Pending')->comment('Status: Pending, Approved, Rejected');
            \$table->unsignedBigInteger('approved_by')->nullable()->comment('Employee who approved/rejected');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            \$table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
}
PHP;
    }

    protected function getPayrollsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    public function up()
    {
        Schema::create('payrolls', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('payroll_id')->unique()->comment('Unique payroll identifier (e.g., PAY-123)');
            \$table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            \$table->string('employee_name');
            \$table->string('period')->comment('Payroll period (Y-m)');
            \$table->decimal('base_salary', 15, 2)->comment('Base salary for the period');
            \$table->decimal('allowances', 15, 2)->nullable()->comment('Total allowances');
            \$table->decimal('deductions', 15, 2)->default(0.00)->comment('Total deductions');
            \$table->decimal('net_salary', 15, 2)->comment('Net salary after deductions');
            \$table->string('status')->default('Pending')->comment('Payroll status: Pending, Processed, Paid');
            \$table->date('payment_date')->nullable()->comment('Date of payment');
            \$table->string('payment_method')->nullable()->comment('Payment method: Bank Transfer, Cheque');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}
PHP;
    }

    protected function getPayrollAlertsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAlertsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_alerts', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('alert_id')->unique()->comment('Unique alert identifier (e.g., ALT-123)');
            \$table->unsignedBigInteger('employee_id')->nullable()->comment('Foreign key to employees');
            \$table->string('type')->comment('Alert type: payroll_processed, payment_due, compliance_due, low_balance');
            \$table->text('message')->comment('Alert message');
            \$table->string('status')->default('Unread')->comment('Alert status: Unread, Read');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_alerts');
    }
}
PHP;
    }

    protected function getPayslipsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    public function up()
    {
        Schema::create('payslips', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('payslip_id')->unique()->comment('Unique payslip identifier (e.g., PSLIP-123)');
            \$table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            \$table->string('employee_name');
            \$table->string('period')->comment('Payslip period (Y-m)');
            \$table->decimal('base_salary', 15, 2)->comment('Base salary for the period');
            \$table->decimal('allowances', 15, 2)->nullable()->comment('Total allowances');
            \$table->decimal('deductions', 15, 2)->default(0.00)->comment('Total deductions');
            \$table->decimal('net_salary', 15, 2)->comment('Net salary after deductions');
            \$table->string('status')->default('Generated')->comment('Payslip status: Generated, Sent, Viewed');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}
PHP;
    }

    protected function getReportsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('report_id')->unique()->comment('Unique identifier for the report (e.g., RPT-123)');
            \$table->string('type')->comment('Report type: payslip, payroll_summary, tax_report, nssf_report, nhif_report, wcf_report, sdl_report, year_end_summary');
            \$table->string('period')->comment('Report period (Y-m for monthly, Y for yearly)');
            \$table->unsignedBigInteger('employee_id')->nullable()->comment('Optional employee ID for employee-specific reports');
            \$table->integer('batch_number')->nullable();
            \$table->string('export_format')->comment('Export format: pdf, excel');
            \$table->unsignedBigInteger('generated_by')->comment('ID of the employee who generated the report');
            \$table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->comment('Report generation status');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            \$table->foreign('generated_by')->references('id')->on('employees')->onDelete('cascade');
            \$table->index('generated_by');
            \$table->index('type');
            \$table->index('period');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}
PHP;
    }

    protected function getSessionsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionsTable extends Migration
{
    public function up()
    {
        Schema::create('sessions', function (Blueprint \$table) {
            \$table->string('id')->primary();
            \$table->unsignedBigInteger('user_id')->nullable()->index();
            \$table->string('ip_address', 45)->nullable();
            \$table->text('user_agent')->nullable();
            \$table->longText('payload');
            \$table->integer('last_activity')->index();
            \$table->foreign('user_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sessions');
    }
}
PHP;
    }

    protected function getTransactionsMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint \$table) {
            \$table->id()->comment('Primary key');
            \$table->string('transaction_id')->unique()->comment('Unique transaction identifier (e.g., TXN-123)');
            \$table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            \$table->string('employee_name');
            \$table->string('type')->comment('Transaction type: salary_payment, bonus, deduction, reimbursement');
            \$table->decimal('amount', 15, 2)->comment('Transaction amount');
            \$table->date('transaction_date')->comment('Date of transaction');
            \$table->string('status')->default('Pending')->comment('Transaction status: Pending, Completed, Failed');
            \$table->string('payment_method')->nullable()->comment('Payment method: Bank Transfer, Cheque');
            \$table->text('description')->nullable()->comment('Transaction description');
            \$table->timestamps();
            \$table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            \$table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
PHP;
    }

    protected function getPasswordResetTokensMigration()
    {
        return <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePasswordResetTokensTable extends Migration
{
    public function up()
    {
        Schema::create('password_reset_tokens', function (Blueprint \$table) {
            \$table->string('email')->primary();
            \$table->string('token');
            \$table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_reset_tokens');
    }
}
PHP;
    }

    // Model templates
    protected function getRoleModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected \$guarded = [];
}
PHP;
    }

    protected function getDepartmentModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use SoftDeletes;

    protected \$guarded = [];
}
PHP;
    }

    protected function getBankModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employees()
    {
        return \$this->hasMany(Employee::class, 'bank_name', 'name');
    }
}
PHP;
    }

    protected function getEmployeeModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes;

    protected \$guarded = [];

    public function attendances()
    {
        return \$this->hasMany(Attendance::class);
    }

    public function complianceTasks()
    {
        return \$this->hasMany(ComplianceTask::class);
    }

    public function leaveRequests()
    {
        return \$this->hasMany(LeaveRequest::class);
    }

    public function payrolls()
    {
        return \$this->hasMany(Payroll::class);
    }

    public function payrollAlerts()
    {
        return \$this->hasMany(PayrollAlert::class);
    }

    public function payslips()
    {
        return \$this->hasMany(Payslip::class);
    }

    public function transactions()
    {
        return \$this->hasMany(Transaction::class);
    }

    public function reports()
    {
        return \$this->hasMany(Report::class, 'generated_by');
    }

    public function sessions()
    {
        return \$this->hasMany(Session::class, 'user_id');
    }

    public function bank()
    {
        return \$this->belongsTo(Bank::class, 'bank_name', 'name');
    }
}
PHP;
    }

    protected function getAttendanceModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getComplianceTaskModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ComplianceTask extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getLeaveRequestModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }

    public function approvedBy()
    {
        return \$this->belongsTo(Employee::class, 'approved_by');
    }
}
PHP;
    }

    protected function getPayrollModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getPayrollAlertModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollAlert extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getPayslipModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getReportModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class, 'employee_id');
    }

    public function generatedBy()
    {
        return \$this->belongsTo(Employee::class, 'generated_by');
    }
}
PHP;
    }

    protected function getSessionModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected \$guarded = [];
    protected \$primaryKey = 'id';
    public \$incrementing = false;
    protected \$keyType = 'string';

    public function employee()
    {
        return \$this->belongsTo(Employee::class, 'user_id');
    }
}
PHP;
    }

    protected function getTransactionModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected \$guarded = [];

    public function employee()
    {
        return \$this->belongsTo(Employee::class);
    }
}
PHP;
    }

    protected function getPasswordResetTokenModel()
    {
        return <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected \$guarded = [];
    protected \$primaryKey = 'email';
    public \$incrementing = false;
    protected \$keyType = 'string';
}
PHP;
    }
}