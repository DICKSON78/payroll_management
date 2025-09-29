<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class RenameAllowancesTable extends Migration
{
    public function up()
    {
        Schema::rename('allowances', 'allowance');
    }

    public function down()
    {
        Schema::rename('allowance', 'allowances');
    }
}