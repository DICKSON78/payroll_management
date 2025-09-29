<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDeductionTable extends Migration
{
    public function up()
    {
        Schema::create('employee_deduction', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 255);
            $table->unsignedBigInteger('deduction_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('deduction_id')->references('id')->on('deductions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_deduction');
    }
}