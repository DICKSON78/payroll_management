<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_id');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('period');
            $table->decimal('base_salary', 15, 2)->nullable();
            $table->decimal('gross_salary', 15, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->decimal('overtime_pay', 15, 2)->nullable();
            $table->decimal('housing_allowance', 15, 2)->nullable();
            $table->decimal('transport_allowance', 15, 2)->nullable();
            $table->decimal('medical_allowance', 15, 2)->nullable();
            $table->decimal('adjustment_amount', 15, 2)->nullable();
            $table->decimal('total_allowances', 15, 2)->nullable();
            $table->decimal('nssf', 15, 2)->nullable();
            $table->decimal('paye', 15, 2)->nullable();
            $table->decimal('nhif', 15, 2)->nullable();
            $table->decimal('wcf', 15, 2)->nullable();
            $table->decimal('sdl', 15, 2)->nullable();
            $table->decimal('total_deductions', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2); // Renamed from net_salary to match your code
            $table->string('status')->default('Pending');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};