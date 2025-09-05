<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compliance_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_id')->unique();
            $table->string('type');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->date('due_date');
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('details')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compliance_tasks');
    }
};