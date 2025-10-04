<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('compliance_tasks', function (Blueprint $table) {
            // Add missing columns without changing employee_id type
            $table->decimal('amount', 15, 2)->nullable()->after('due_date');
            $table->text('details')->nullable()->after('amount');
            $table->timestamp('submitted_at')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('compliance_tasks', function (Blueprint $table) {
            $table->dropColumn(['amount', 'details', 'submitted_at']);
        });
    }
};