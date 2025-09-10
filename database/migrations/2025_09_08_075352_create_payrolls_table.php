<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Creates the 'payrolls' table to store payroll run summaries.
        // This table is a prerequisite for other features that depend on payroll data.
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_id')->unique();
            $table->string('period'); // e.g., '2025-09'
            $table->decimal('total_amount', 15, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payrolls');
    }
}
