<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAllowanceTable extends Migration
{
    public function up()
    {
        Schema::create('employee_allowance', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 255);
            $table->unsignedBigInteger('allowance_id');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('allowance_id')->references('id')->on('allowance')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_allowance');
    }
}