<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Add session and authentication fields
            if (!Schema::hasColumn('employees', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'last_logout_at')) {
                $table->timestamp('last_logout_at')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'auto_logout')) {
                $table->boolean('auto_logout')->default(false);
            }
            
            if (!Schema::hasColumn('employees', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable();
            }
            
            if (!Schema::hasColumn('employees', 'remember_token')) {
                $table->rememberToken();
            }
            
            if (!Schema::hasColumn('employees', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'last_login_at',
                'last_login_ip', 
                'last_logout_at',
                'auto_logout',
                'password_changed_at',
                'remember_token',
                'email_verified_at'
            ]);
        });
    }
};