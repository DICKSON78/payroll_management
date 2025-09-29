<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deduction extends Model
{
    protected $fillable = [
        'name',
        'type',
        'category',
        'amount',
        'active',
        'description',
        'statutory_type', // nssf, nhif, paye, etc.
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'active' => 'boolean',
    ];

    /**
     * Get payrolls that include this deduction
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class, 'deduction_id');
    }

    /**
     * Get payslips that include this deduction
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'deduction_id');
    }

    /**
     * Scope for active deductions
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope for statutory deductions
     */
    public function scopeStatutory($query)
    {
        return $query->where('category', 'statutory');
    }

    /**
     * Scope for voluntary deductions
     */
    public function scopeVoluntary($query)
    {
        return $query->where('category', 'voluntary');
    }

    /**
     * Calculate deduction amount based on gross salary
     */
    public function calculateAmount($grossSalary)
    {
        if ($this->type === 'percentage') {
            return $grossSalary * ($this->amount / 100);
        }

        return $this->amount;
    }
}
