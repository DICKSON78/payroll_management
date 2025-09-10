<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('department');
            $table->string('position');
            $table->decimal('base_salary', 15, 2);
            $table->decimal('allowances', 15, 2)->nullable();
            $table->decimal('deductions', 15, 2)->default(0); // <--- Added deductions
            $table->string('status')->default('Active');
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('nationality')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->date('hire_date');
            $table->date('contract_end_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('employment_type')->default('Full-Time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
