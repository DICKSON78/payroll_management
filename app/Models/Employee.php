<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'id',
        'employee_id',
        'name',
        'email',
        'department',
        'position',
        'base_salary',
        'allowances',
        'deductions',
        'status',
        'gender',
        'dob',
        'nationality',
        'phone',
        'address',
        'hire_date',
        'contract_end_date',
        'bank_name',
        'account_number',
        'employment_type',
        'nssf_number',
        'nhif_number',
        'tin_number',
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Relation to User model (based on email)
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'email', 'email');
    }

    /**
     * Relation to Payslips
     */
    public function payslips()
    {
        return $this->hasMany(\App\Models\Payslip::class, 'employee_id', 'id');
    }
}