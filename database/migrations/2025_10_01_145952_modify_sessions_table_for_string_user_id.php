<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify sessions table to use string user_id
        Schema::table('sessions', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['user_id']);
            
            // Change user_id column to string
            $table->string('user_id', 50)->nullable()->change();
            
            // Add foreign key constraint referencing employee_id
            $table->foreign('user_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }
};