<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollAlert extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'alert_id',
        'employee_id',
        'type',
        'message',
        'status',
        'metadata'
    ];

    /**
     * Relationship with employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Scope for unread alerts
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'Unread');
    }

    /**
     * Scope for read alerts
     */
    public function scopeRead($query)
    {
        return $query->where('status', 'Read');
    }

    /**
     * Scope for retroactive alerts
     */
    public function scopeRetroactive($query)
    {
        return $query->where('type', 'like', '%Retroactive%');
    }

    /**
     * Scope for pending approval alerts
     */
    public function scopePendingApproval($query)
    {
        return $query->where('type', 'like', '%Pending%')
                    ->where('status', 'Unread');
    }

    /**
     * Check if alert requires action
     */
    public function requiresAction()
    {
        $metadata = $this->metadata ?? [];
        return $metadata['action_required'] ?? false;
    }

    /**
     * Get adjustment ID from metadata
     */
    public function getAdjustmentId()
    {
        $metadata = $this->metadata ?? [];
        return $metadata['adjustment_id'] ?? null;
    }

    /**
     * Mark alert as read
     */
    public function markAsRead()
    {
        $this->update(['status' => 'Read']);
    }

    /**
     * Mark alert as unread
     */
    public function markAsUnread()
    {
        $this->update(['status' => 'Unread']);
    }

    /**
     * Update alert type and message
     */
    public function updateAlert($type, $message, $metadata = null)
    {
        $updateData = [
            'type' => $type,
            'message' => $message,
        ];

        if ($metadata !== null) {
            $updateData['metadata'] = $metadata;
        }

        $this->update($updateData);
    }

    /**
     * Check if alert is retroactive type
     */
    public function isRetroactiveAlert()
    {
        return str_contains(strtolower($this->type), 'retroactive');
    }

    /**
     * Check if alert is pending approval
     */
    public function isPendingApproval()
    {
        return str_contains(strtolower($this->type), 'pending') &&
               $this->status === 'Unread';
    }

    /**
     * Create a new retroactive pending alert
     */
    public static function createRetroactivePendingAlert($employeeId, $adjustmentId, $type, $amount, $period, $reason)
    {
        $alertId = self::generateAlertId();

        return self::create([
            'alert_id' => $alertId,
            'employee_id' => $employeeId,
            'type' => 'Retroactive Adjustment Pending',
            'message' => "Retroactive {$type} of TZS " . number_format($amount, 0) . " is pending approval for period {$period}. Reason: {$reason}",
            'status' => 'Unread',
            'metadata' => [
                'adjustment_id' => $adjustmentId,
                'type' => 'retroactive_approval',
                'action_required' => true,
                'amount' => $amount,
                'period' => $period,
                'adjustment_type' => $type,
                'reason' => $reason
            ]
        ]);
    }

    /**
     * Create a new retroactive approved alert
     */
    public static function createRetroactiveApprovedAlert($employeeId, $adjustmentId, $type, $amount, $period)
    {
        $alertId = self::generateAlertId();

        return self::create([
            'alert_id' => $alertId,
            'employee_id' => $employeeId,
            'type' => 'Retroactive Adjustment Approved',
            'message' => "Retroactive {$type} of TZS " . number_format($amount, 0) . " has been approved for period {$period} and will be applied in next payroll.",
            'status' => 'Unread',
            'metadata' => [
                'adjustment_id' => $adjustmentId,
                'type' => 'retroactive_approved',
                'action_required' => false,
                'amount' => $amount,
                'period' => $period
            ]
        ]);
    }

    /**
     * Create a new retroactive applied alert
     */
    public static function createRetroactiveAppliedAlert($employeeId, $adjustmentId, $type, $amount, $period)
    {
        $alertId = self::generateAlertId();

        return self::create([
            'alert_id' => $alertId,
            'employee_id' => $employeeId,
            'type' => 'Retroactive Adjustment Applied',
            'message' => "Retroactive {$type} of TZS " . number_format($amount, 0) . " has been successfully applied in payroll for period {$period}.",
            'status' => 'Unread',
            'metadata' => [
                'adjustment_id' => $adjustmentId,
                'type' => 'retroactive_applied',
                'action_required' => false,
                'amount' => $amount,
                'period' => $period
            ]
        ]);
    }

    /**
     * Generate unique alert ID
     */
    private static function generateAlertId()
    {
        $maxAttempts = 10;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            $random = strtoupper(bin2hex(random_bytes(4)));
            $alertId = 'ALRT-' . $random;

            if (!self::where('alert_id', $alertId)->exists()) {
                return $alertId;
            }

            $attempt++;
        }

        $timestamp = now()->format('YmdHis');
        return 'ALRT-' . $timestamp . '-' . rand(1000, 9999);
    }
}
