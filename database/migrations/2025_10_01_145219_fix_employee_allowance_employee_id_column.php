<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify employee_allowance table
        Schema::table('employee_allowance', function (Blueprint $table) {
            // Drop existing foreign key
            $table->dropForeign(['employee_id']);
            
            // Change column type to string
            $table->string('employee_id')->change();
            
            // Add foreign key constraint
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });

        // Do the same for employee_deduction
        Schema::table('employee_deduction', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->string('employee_id')->change();
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Revert changes
        Schema::table('employee_allowance', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->unsignedBigInteger('employee_id')->change();
        });

        Schema::table('employee_deduction', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->unsignedBigInteger('employee_id')->change();
        });
    }
};