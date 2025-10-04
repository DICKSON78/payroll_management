<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('compliance_tasks', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['employee_id']);
            
            // Change employee_id to nullable
            $table->string('employee_id')->nullable()->change();
            
            // Re-add foreign key constraint (only for non-null values)
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('compliance_tasks', function (Blueprint $table) {
            // Drop foreign key constraint
            $table->dropForeign(['employee_id']);
            
            // Change employee_id back to not nullable
            $table->string('employee_id')->nullable(false)->change();
            
            // Re-add foreign key constraint
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }
};