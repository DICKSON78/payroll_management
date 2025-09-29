<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBanksTable extends Migration
{
    public function up()
    {
        Schema::create('banks', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('name')->unique()->comment('Bank name');
            $table->string('code')->unique()->nullable()->comment('Bank code');
            $table->string('swift_code')->nullable()->comment('SWIFT/BIC code');
            $table->string('contact_email')->nullable()->comment('Contact email');
            $table->string('contact_phone')->nullable()->comment('Contact phone');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('banks');
    }
}