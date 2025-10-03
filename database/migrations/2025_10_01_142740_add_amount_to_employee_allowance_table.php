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
        Schema::table('employee_allowance', function (Blueprint $table) {
            // Check if column doesn't exist before adding it
            if (!Schema::hasColumn('employee_allowance', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('allowance_id');
            }
        });

        // Also add to employee_deduction if needed
        Schema::table('employee_deduction', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_deduction', 'amount')) {
                $table->decimal('amount', 10, 2)->default(0)->after('deduction_id');
            }
        });

        // Add active column to employee_deduction if needed
        Schema::table('employee_deduction', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_deduction', 'active')) {
                $table->boolean('active')->default(1)->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_allowance', function (Blueprint $table) {
            $table->dropColumn('amount');
        });

        Schema::table('employee_deduction', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('active');
        });
    }
};