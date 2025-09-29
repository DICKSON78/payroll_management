<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollAlertsTable extends Migration
{
    public function up()
    {
        Schema::create('payroll_alerts', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('alert_id')->unique()->comment('Unique alert identifier (e.g., ALT-123)');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('Foreign key to employees');
            $table->string('type')->comment('Alert type: payroll_processed, payment_due, compliance_due, low_balance');
            $table->text('message')->comment('Alert message');
            $table->string('status')->default('Unread')->comment('Alert status: Unread, Read');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_alerts');
    }
}