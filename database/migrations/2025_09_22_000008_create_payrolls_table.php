<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('payroll_id')->unique()->comment('Unique payroll identifier (e.g., PAY-123)');
            $table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            $table->string('employee_name');
            $table->string('period')->comment('Payroll period (Y-m) - e.g., 2025-09');
            $table->decimal('base_salary', 15, 2)->comment('Base salary for the period');
            $table->decimal('allowances', 15, 2)->nullable()->comment('Total allowances');
            $table->decimal('deductions', 15, 2)->default(0.00)->comment('Total deductions');
            $table->decimal('net_salary', 15, 2)->comment('Net salary after deductions');
            $table->string('status')->default('Pending')->comment('Payroll status: Pending, Processed, Paid');
            $table->date('payment_date')->nullable()->comment('Date of payment');
            $table->string('payment_method')->nullable()->comment('Payment method: Bank Transfer, Cheque');
            $table->timestamps();
            
            // Kurekebisha: Kutumia 'employees' badala ya 'users' kwa kulinganisha mtumiaji aliyeunda payroll
            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null');
            
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');

            // Uhusiano wa Foreign Key kwa mfanyakazi
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
}
