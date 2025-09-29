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
            $table->string('type')->comment('Report type: payslip, payroll_summary, tax_report, nssf_report, nhif_report, wcf_report, sdl_report, year_end_summary');
            $table->string('period')->comment('Report period (Y-m for monthly, Y for yearly)');
            $table->unsignedBigInteger('employee_id')->nullable()->comment('Optional employee ID for employee-specific reports');
            $table->integer('batch_number')->nullable();
            $table->string('export_format')->comment('Export format: pdf, excel');
            $table->unsignedBigInteger('generated_by')->comment('ID of the employee who generated the report');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->comment('Report generation status');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('generated_by')->references('id')->on('employees')->onDelete('cascade');
            $table->index('generated_by');
            $table->index('type');
            $table->index('period');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reports');
    }
}