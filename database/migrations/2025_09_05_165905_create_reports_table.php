<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    public function up()
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('report_id')->unique()->comment('Unique identifier for the report (e.g., RPT-123)');
            $table->string('type')->index()->comment('Report type: payslip, payroll_summary, tax_report, nssf_report, nhif_report, wcf_report, sdl_report, year_end_summary');
            $table->string('period')->index()->comment('Report period (Y-m for monthly, Y for yearly)');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('Optional employee ID for employee-specific reports');
            $table->string('export_format')->comment('Export format: pdf, excel');
            $table->unsignedBigInteger('generated_by')->comment('ID of the user who generated the report');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->comment('Report generation status');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');

            // Foreign key constraints
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null')
                ->comment('Foreign key to employees table, set null on employee deletion');
            $table->foreign('generated_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade')
                ->comment('Foreign key to users table, cascade on user deletion');

            // Additional index for generated_by to optimize user-specific queries
            $table->index('generated_by', 'reports_generated_by_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}