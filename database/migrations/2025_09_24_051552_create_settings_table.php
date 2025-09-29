<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('key')->unique()->comment('Setting key identifier');
            $table->text('value')->nullable()->comment('Setting value (can be JSON)');
            $table->string('type')->default('string')->comment('Value type: string, integer, boolean, array, json');
            $table->string('category')->default('general')->comment('Setting category: payroll, notifications, integrations, allowances, deductions');
            $table->text('description')->nullable()->comment('Setting description');
            $table->boolean('is_public')->default(false)->comment('Whether setting is publicly accessible');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Employee who last updated this setting');
            $table->timestamps();
            $table->softDeletes()->comment('Soft delete timestamp for recoverable deletion');

            $table->foreign('updated_by')->references('id')->on('employees')->onDelete('set null');
            $table->index('key');
            $table->index('category');
        });

        // Insert default settings
        $this->insertDefaultSettings();
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }

    private function insertDefaultSettings()
    {
        $defaultSettings = [
            // Payroll Settings
            [
                'key' => 'pay_schedule',
                'value' => 'monthly',
                'type' => 'string',
                'category' => 'payroll',
                'description' => 'Payroll processing schedule',
                'is_public' => false,
            ],
            [
                'key' => 'processing_day',
                'value' => '25',
                'type' => 'integer',
                'category' => 'payroll',
                'description' => 'Day of month for payroll processing',
                'is_public' => false,
            ],
            [
                'key' => 'default_currency',
                'value' => 'TZS',
                'type' => 'string',
                'category' => 'payroll',
                'description' => 'Default currency for payroll',
                'is_public' => true,
            ],
            [
                'key' => 'overtime_calculation',
                'value' => '1.5x',
                'type' => 'string',
                'category' => 'payroll',
                'description' => 'Overtime rate calculation method',
                'is_public' => false,
            ],

            // Tanzanian Statutory Settings
            [
                'key' => 'nssf_employer_rate',
                'value' => '10.0',
                'type' => 'decimal',
                'category' => 'payroll',
                'description' => 'NSSF employer contribution rate (%)',
                'is_public' => true,
            ],
            [
                'key' => 'nssf_employee_rate',
                'value' => '10.0',
                'type' => 'decimal',
                'category' => 'payroll',
                'description' => 'NSSF employee contribution rate (%)',
                'is_public' => true,
            ],
            [
                'key' => 'nhif_calculation_method',
                'value' => 'tiered',
                'type' => 'string',
                'category' => 'payroll',
                'description' => 'NHIF contribution calculation method',
                'is_public' => false,
            ],
            [
                'key' => 'paye_tax_free',
                'value' => '270000',
                'type' => 'integer',
                'category' => 'payroll',
                'description' => 'PAYE tax-free threshold (TZS)',
                'is_public' => true,
            ],
            [
                'key' => 'wcf_rate',
                'value' => '0.5',
                'type' => 'decimal',
                'category' => 'payroll',
                'description' => 'Workers Compensation Fund rate (%)',
                'is_public' => false,
            ],
            [
                'key' => 'sdl_rate',
                'value' => '3.5',
                'type' => 'decimal',
                'category' => 'payroll',
                'description' => 'Skills Development Levy rate (%)',
                'is_public' => false,
            ],

            // Notification Settings
            [
                'key' => 'email_notifications',
                'value' => json_encode(['payroll_processing', 'payment_confirmation']),
                'type' => 'array',
                'category' => 'notifications',
                'description' => 'Enabled email notification types',
                'is_public' => false,
            ],
            [
                'key' => 'sms_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'notifications',
                'description' => 'Enable SMS notifications',
                'is_public' => false,
            ],
            [
                'key' => 'sms_gateway',
                'value' => 'twilio',
                'type' => 'string',
                'category' => 'notifications',
                'description' => 'SMS gateway provider',
                'is_public' => false,
            ],

            // Integration Settings
            [
                'key' => 'accounting_software',
                'value' => '',
                'type' => 'string',
                'category' => 'integrations',
                'description' => 'Integrated accounting software',
                'is_public' => false,
            ],
            [
                'key' => 'attendance_sync',
                'value' => 'false',
                'type' => 'boolean',
                'category' => 'integrations',
                'description' => 'Enable attendance data sync',
                'is_public' => false,
            ],
        ];

        foreach ($defaultSettings as $setting) {
            DB::table('settings')->insert(array_merge($setting, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
