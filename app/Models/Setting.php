<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Setting extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'type',
        'category',
        'description',
        'is_public',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_public' => 'boolean',
        'value' => 'array', // For storing JSON settings
    ];

    /**
     * Get the employee who last updated this setting
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    /**
     * Scope for payroll settings
     */
    public function scopePayroll($query)
    {
        return $query->where('category', 'payroll');
    }

    /**
     * Scope for notification settings
     */
    public function scopeNotifications($query)
    {
        return $query->where('category', 'notifications');
    }

    /**
     * Scope for integration settings
     */
    public function scopeIntegrations($query)
    {
        return $query->where('category', 'integrations');
    }

    /**
     * Scope for allowance settings
     */
    public function scopeAllowances($query)
    {
        return $query->where('category', 'allowances');
    }

    /**
     * Scope for deduction settings
     */
    public function scopeDeductions($query)
    {
        return $query->where('category', 'deductions');
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Get setting value by key with caching
     */
    public static function getValue(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $category = 'general', int $updatedBy = null): bool
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'category' => $category,
                'updated_by' => $updatedBy ?? auth()->id(),
            ]
        );

        return $setting instanceof Setting;
    }

    /**
     * Get all settings as key-value pairs
     */
    public static function getAllSettings(): array
    {
        return static::pluck('value', 'key')->toArray();
    }

    /**
     * Get payroll-specific settings
     */
    public static function getPayrollSettings(): array
    {
        return static::payroll()->pluck('value', 'key')->toArray();
    }

    /**
     * Get notification settings
     */
    public static function getNotificationSettings(): array
    {
        return static::notifications()->pluck('value', 'key')->toArray();
    }

    /**
     * Get integration settings
     */
    public static function getIntegrationSettings(): array
    {
        return static::integrations()->pluck('value', 'key')->toArray();
    }

    /**
     * Bulk update settings
     */
    public static function updateSettings(array $settings, int $updatedBy = null): bool
    {
        try {
            foreach ($settings as $key => $value) {
                static::setValue($key, $value, 'general', $updatedBy);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Tanzanian statutory settings
     */
    public static function getTanzanianStatutorySettings(): array
    {
        return [
            'nssf_employer_rate' => static::getValue('nssf_employer_rate', 10.0),
            'nssf_employee_rate' => static::getValue('nssf_employee_rate', 10.0),
            'nhif_calculation_method' => static::getValue('nhif_calculation_method', 'tiered'),
            'paye_tax_free' => static::getValue('paye_tax_free', 270000),
            'wcf_rate' => static::getValue('wcf_rate', 0.5),
            'sdl_rate' => static::getValue('sdl_rate', 3.5),
        ];
    }

    /**
     * Get payroll configuration settings
     */
    public static function getPayrollConfiguration(): array
    {
        return [
            'pay_schedule' => static::getValue('pay_schedule', 'monthly'),
            'processing_day' => static::getValue('processing_day', 25),
            'default_currency' => static::getValue('default_currency', 'TZS'),
            'overtime_calculation' => static::getValue('overtime_calculation', '1.5x'),
            'working_days_per_week' => static::getValue('working_days_per_week', 5),
            'daily_working_hours' => static::getValue('daily_working_hours', 8),
        ];
    }

    /**
     * Get notification configuration
     */
    public static function getNotificationConfiguration(): array
    {
        return [
            'email_notifications' => static::getValue('email_notifications', []),
            'sms_enabled' => static::getValue('sms_enabled', false),
            'sms_gateway' => static::getValue('sms_gateway', 'twilio'),
            'sms_balance_alert' => static::getValue('sms_balance_alert', 100),
        ];
    }

    /**
     * Get integration configuration
     */
    public static function getIntegrationConfiguration(): array
    {
        return [
            'accounting_software' => static::getValue('accounting_software', ''),
            'api_key' => static::getValue('api_key', ''),
            'bank_api' => static::getValue('bank_api', ''),
            'bank_endpoint' => static::getValue('bank_endpoint', ''),
            'attendance_sync' => static::getValue('attendance_sync', false),
            'sync_frequency' => static::getValue('sync_frequency', 'daily'),
        ];
    }
}
