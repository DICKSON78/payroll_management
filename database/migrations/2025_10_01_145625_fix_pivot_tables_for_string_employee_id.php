<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix employee_allowance table
        Schema::table('employee_allowance', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['employee_id']);
            
            // Change column to string to match employee_id
            $table->string('employee_id', 50)->change();
            
            // Add correct foreign key
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });

        // Fix employee_deduction table
        Schema::table('employee_deduction', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->string('employee_id', 50)->change();
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Revert changes if needed
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