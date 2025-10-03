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
        Schema::table('payroll_alerts', function (Blueprint $table) {
            $table->json('metadata')->nullable()->after('status')->comment('Additional alert data in JSON format');

            // Add index for better performance on common queries
            $table->index(['type', 'status']);
            $table->index(['employee_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_alerts', function (Blueprint $table) {
            $table->dropIndex(['type', 'status']);
            $table->dropIndex(['employee_id', 'status']);
            $table->dropColumn('metadata');
        });
    }
};
