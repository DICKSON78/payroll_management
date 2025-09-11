<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Add generated_by column if it doesn't exist
            if (!Schema::hasColumn('reports', 'generated_by')) {
                $table->unsignedBigInteger('generated_by')->after('export_format')->comment('ID of the user who generated the report');
                $table->foreign('generated_by')
                    ->references('id')
                    ->on('users')
                    ->onDelete('cascade')
                    ->comment('Foreign key to users table, cascade on user deletion');
            }

            // Add status column if it doesn't exist
            if (!Schema::hasColumn('reports', 'status')) {
                $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->after('generated_by')->comment('Report generation status');
            }

            // Add soft deletes if not already present
            if (!Schema::hasColumn('reports', 'deleted_at')) {
                $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            }

            // Add indexes if they don't exist
            if (!Schema::hasIndex('reports', 'reports_type_index')) {
                $table->index('type', 'reports_type_index');
            }
            if (!Schema::hasIndex('reports', 'reports_period_index')) {
                $table->index('period', 'reports_period_index');
            }
            if (!Schema::hasIndex('reports', 'reports_generated_by_index')) {
                $table->index('generated_by', 'reports_generated_by_index');
            }
        });

        // Update existing reports with default values for new columns
        if (Schema::hasColumn('reports', 'generated_by')) {
            \App\Models\Report::whereNull('generated_by')->update(['generated_by' => 1]); // Default to admin user ID 1
        }
        if (Schema::hasColumn('reports', 'status')) {
            \App\Models\Report::whereNull('status')->update(['status' => 'completed']);
        }
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop foreign key and column if they exist
            if (Schema::hasColumn('reports', 'generated_by')) {
                $table->dropForeign(['generated_by']);
                $table->dropColumn('generated_by');
            }

            // Drop status column if it exists
            if (Schema::hasColumn('reports', 'status')) {
                $table->dropColumn('status');
            }

            // Drop soft deletes if it exists
            if (Schema::hasColumn('reports', 'deleted_at')) {
                $table->dropColumn('deleted_at');
            }

            // Drop indexes if they exist
            if (Schema::hasIndex('reports', 'reports_type_index')) {
                $table->dropIndex('reports_type_index');
            }
            if (Schema::hasIndex('reports', 'reports_period_index')) {
                $table->dropIndex('reports_period_index');
            }
            if (Schema::hasIndex('reports', 'reports_generated_by_index')) {
                $table->dropIndex('reports_generated_by_index');
            }
        });
    }
};