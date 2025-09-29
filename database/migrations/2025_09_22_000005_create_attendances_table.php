<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public function up()
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            $table->string('employee_name');
            $table->date('date')->comment('Date of attendance');
            $table->time('check_in')->nullable()->comment('Check-in time');
            $table->time('check_out')->nullable()->comment('Check-out time');
            $table->string('status')->default('Present')->comment('Attendance status: Present, Absent, Leave, Holiday');
            $table->decimal('hours_worked', 5, 2)->nullable()->comment('Total hours worked');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}