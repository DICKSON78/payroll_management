<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->nullable()->after('allowances');
        });

        // Update existing records
        \App\Models\Payroll::query()->update([
            'total_amount' => \DB::raw('base_salary + COALESCE(allowances, 0)')
        ]);

        // Make it not nullable after populating data
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn('total_amount');
        });
    }
};
