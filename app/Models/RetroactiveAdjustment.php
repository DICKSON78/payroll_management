<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RetroactiveAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';

    protected $fillable = [
        'adjustment_id',
        'employee_id',
        'period',
        'type',
        'amount',
        'reason',
        'status',
        'applied_at',
        'approved_by',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'applied_at' => 'datetime',
    ];

    /**
     * Relationship with employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }

    /**
     * Relationship with creator
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    /**
     * Relationship with approver
     */
    public function approver()
    {
        return $this->belongsTo(Employee::class, 'approved_by', 'id');
    }

    /**
     * Scope for pending adjustments
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved adjustments
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for applied adjustments
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    /**
     * Scope for period
     */
    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Check if adjustment is applicable
     */
    public function isApplicable()
    {
        return $this->status === 'approved';
    }

    /**
     * Mark as approved
     */
    public function markAsApproved($approvedBy)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy
        ]);
    }

    /**
     * Mark as applied
     */
    public function markAsApplied()
    {
        $this->update([
            'status' => 'applied',
            'applied_at' => now()
        ]);
    }

    /**
     * Mark as reverted
     */
    public function markAsReverted()
    {
        $this->update(['status' => 'reverted']);
    }
}
