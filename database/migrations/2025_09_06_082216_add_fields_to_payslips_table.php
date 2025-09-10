<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPayslipsTable extends Migration
{
    public function up()
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->decimal('overtime_hours', 5, 2)->nullable()->after('sdl');
            $table->decimal('overtime_pay', 15, 2)->nullable()->after('overtime_hours');
            $table->decimal('housing_allowance', 15, 2)->nullable()->after('overtime_pay');
            $table->decimal('transport_allowance', 15, 2)->nullable()->after('housing_allowance');
            $table->decimal('medical_allowance', 15, 2)->nullable()->after('transport_allowance');
            $table->decimal('adjustment_amount', 15, 2)->nullable()->after('medical_allowance');
        });
    }

    public function down()
    {
        Schema::table('payslips', function (Blueprint $table) {
            $table->dropColumn([
                'overtime_hours',
                'overtime_pay',
                'housing_allowance',
                'transport_allowance',
                'medical_allowance',
                'adjustment_amount',
            ]);
        });
    }
}
