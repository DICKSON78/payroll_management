<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retroactive_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_id')->unique();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('period', 7); // Y-m format
            $table->enum('type', ['allowance', 'deduction', 'salary_adjustment']);
            $table->decimal('amount', 15, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'applied', 'reverted'])->default('pending');
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['employee_id', 'period']);
            $table->index(['status', 'period']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retroactive_adjustments');
    }
};
