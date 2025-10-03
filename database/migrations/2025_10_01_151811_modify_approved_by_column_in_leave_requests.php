<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modify leave_requests table - approved_by column
        Schema::table('leave_requests', function (Blueprint $table) {
            // Drop existing foreign key if it exists
            $table->dropForeign(['approved_by']);
            
            // Change approved_by column to string
            $table->string('approved_by', 50)->nullable()->change();
            
            // Add foreign key constraint referencing employee_id
            $table->foreign('approved_by')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });

        // Also check other tables that might have similar approved_by/created_by/updated_by columns
        $tablesWithApprover = ['reports', 'compliance_tasks']; // Add other tables if needed
        
        foreach ($tablesWithApprover as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'approved_by')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['approved_by']);
                    $table->string('approved_by', 50)->nullable()->change();
                    $table->foreign('approved_by')
                          ->references('employee_id')
                          ->on('employees')
                          ->onDelete('cascade');
                });
            }
        }

        // Also check for generated_by columns
        if (Schema::hasTable('reports') && Schema::hasColumn('reports', 'generated_by')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropForeign(['generated_by']);
                $table->string('generated_by', 50)->nullable()->change();
                $table->foreign('generated_by')
                      ->references('employee_id')
                      ->on('employees')
                      ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        // Revert changes
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->unsignedBigInteger('approved_by')->nullable()->change();
        });

        $tablesWithApprover = ['reports', 'compliance_tasks'];
        
        foreach ($tablesWithApprover as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'approved_by')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['approved_by']);
                    $table->unsignedBigInteger('approved_by')->nullable()->change();
                });
            }
        }

        if (Schema::hasTable('reports') && Schema::hasColumn('reports', 'generated_by')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropForeign(['generated_by']);
                $table->unsignedBigInteger('generated_by')->nullable()->change();
            });
        }
    }
};