<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayslipsTable extends Migration
{
    public function up()
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('payslip_id')->unique()->comment('Unique payslip identifier (e.g., PSLIP-123)');
            $table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            $table->string('employee_name');
            $table->string('period')->comment('Payslip period (Y-m)');
            $table->decimal('base_salary', 15, 2)->comment('Base salary for the period');
            $table->decimal('allowances', 15, 2)->nullable()->comment('Total allowances');
            $table->decimal('deductions', 15, 2)->default(0.00)->comment('Total deductions');
            $table->decimal('net_salary', 15, 2)->comment('Net salary after deductions');
            $table->string('status')->default('Generated')->comment('Payslip status: Generated, Sent, Viewed');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payslips');
    }
}