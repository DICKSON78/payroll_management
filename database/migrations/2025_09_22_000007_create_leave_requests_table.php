<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('request_id')->unique()->comment('Unique request identifier (e.g., LRQ-123)');
            $table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            $table->string('employee_name');
            $table->string('leave_type')->comment('Leave type: Annual, Sick, Maternity, Unpaid');
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days')->comment('Number of leave days');
            $table->text('reason')->nullable()->comment('Reason for leave');
            $table->string('status')->default('Pending')->comment('Status: Pending, Approved, Rejected');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Employee who approved/rejected');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('leave_requests');
    }
}