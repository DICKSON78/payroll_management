<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('transaction_id')->unique()->comment('Unique transaction identifier (e.g., TXN-123)');
            $table->unsignedBigInteger('employee_id')->comment('Foreign key to employees');
            $table->string('employee_name');
            $table->string('type')->comment('Transaction type: salary_payment, bonus, deduction, reimbursement');
            $table->decimal('amount', 15, 2)->comment('Transaction amount');
            $table->date('transaction_date')->comment('Date of transaction');
            $table->string('status')->default('Pending')->comment('Transaction status: Pending, Completed, Failed');
            $table->string('payment_method')->nullable()->comment('Payment method: Bank Transfer, Cheque');
            $table->text('description')->nullable()->comment('Transaction description');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}