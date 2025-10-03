<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add amount to employee_allowance pivot table
        Schema::table('employee_allowance', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_allowance', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('allowance_id');
            }
        });

        // Add amount to employee_deduction pivot table
        Schema::table('employee_deduction', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_deduction', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('deduction_id');
            }
        });

        // Add active to employee_deduction if it doesn't exist
        Schema::table('employee_deduction', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_deduction', 'active')) {
                $table->boolean('active')->default(true)->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_allowance', function (Blueprint $table) {
            if (Schema::hasColumn('employee_allowance', 'amount')) {
                $table->dropColumn('amount');
            }
        });

        Schema::table('employee_deduction', function (Blueprint $table) {
            if (Schema::hasColumn('employee_deduction', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('employee_deduction', 'active')) {
                $table->dropColumn('active');
            }
        });
    }
};