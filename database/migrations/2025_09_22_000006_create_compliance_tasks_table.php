<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComplianceTasksTable extends Migration
{
    public function up()
    {
        Schema::create('compliance_tasks', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('task_id')->unique()->comment('Unique task identifier (e.g., CTASK-123)');
            $table->string('type')->comment('Task type: tax_filing, nssf_submission, nhif_submission, wcf_submission, sdl_submission');
            $table->string('description')->nullable()->comment('Task description');
            $table->date('due_date')->comment('Due date for the task');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('Assigned employee');
            $table->string('status')->default('Pending')->comment('Task status: Pending, Completed, Overdue');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compliance_tasks');
    }
}