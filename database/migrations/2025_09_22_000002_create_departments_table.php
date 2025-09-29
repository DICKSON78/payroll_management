<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepartmentsTable extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('name')->comment('Department name');
            $table->text('description')->nullable()->comment('Department description');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('departments');
    }
}