<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'employee_name',
        'period',
        'base_salary',
        'allowances',
        'total_amount',
        'deductions',
        'net_salary',
        'employer_contributions',
        'status',
        'payment_method',
        'created_by'
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'employer_contributions' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // FIXED: Use correct relationship names (plural)
    public function allowances()
    {
        return $this->belongsToMany(Allowance::class, 'employee_allowance')
                    ->withPivot('amount')
                    ->withTimestamps();
    }

    public function deductions()
    {
        return $this->belongsToMany(Deduction::class, 'employee_deduction')
                    ->withPivot('amount')
                    ->withTimestamps();
    }
}