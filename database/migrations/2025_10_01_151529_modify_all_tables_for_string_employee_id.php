<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // List all tables that use employee_id
        $tables = [
            'attendances',
            'leave_requests', 
            'payrolls',
            'payslips',
            'compliance_tasks',
            'reports',
            'transactions',
            'payroll_alerts',
            'employee_allowance',
            'employee_deduction'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Drop existing foreign key if it exists
                    $table->dropForeign(['employee_id']);
                    
                    // Change employee_id column to string
                    $table->string('employee_id', 50)->change();
                    
                    // Add foreign key constraint referencing employee_id in employees table
                    $table->foreign('employee_id')
                          ->references('employee_id')
                          ->on('employees')
                          ->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        // Revert changes
        $tables = [
            'attendances',
            'leave_requests',
            'payrolls',
            'payslips', 
            'compliance_tasks',
            'reports',
            'transactions',
            'payroll_alerts',
            'employee_allowance',
            'employee_deduction'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['employee_id']);
                    $table->unsignedBigInteger('employee_id')->change();
                });
            }
        }
    }
};