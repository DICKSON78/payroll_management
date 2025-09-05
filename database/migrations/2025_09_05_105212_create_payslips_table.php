<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('payroll_id');
            $table->string('period');
            $table->decimal('gross_salary', 15, 2);
            $table->decimal('nssf', 15, 2);
            $table->decimal('paye', 15, 2);
            $table->decimal('nhif', 15, 2);
            $table->decimal('other_deductions', 15, 2)->nullable();
            $table->decimal('net_salary', 15, 2);
            $table->string('status')->default('Pending');
            $table->decimal('wcf', 15, 2)->nullable();
            $table->decimal('sdl', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};