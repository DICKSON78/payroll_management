<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model implements AuthenticatableContract
{
    use Authenticatable, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'dob' => 'date',
        'hire_date' => 'date',
        'contract_end_date' => 'date',
        'base_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
    ];

    // FIX: Relationship ya allowances
    public function allowances()
    {
        return $this->belongsToMany(
            Allowance::class,
            'employee_allowance',
            'employee_id',      // foreign pivot key (EMP-XXXX kwenye pivot)
            'allowance_id',     // related pivot key
            'employee_id',      // local key (EMP-XXXX kwenye employees)
            'id'                // related key (id kwenye allowances)
        )->withTimestamps();
    }

    public function departmentRel()
    {
        return $this->belongsTo(Department::class, 'department', 'id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_name', 'name');
    }

    public function deductions()
    {
        return $this->belongsToMany(Deduction::class, 'employee_deduction', 'employee_id', 'deduction_id')
                    ->withPivot('id')
                    ->withTimestamps();
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id', 'employee_id');
    }

    public function complianceTasks()
    {
        return $this->hasMany(ComplianceTask::class, 'employee_id', 'employee_id');
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id', 'employee_id');
    }

    public function payrollAlerts()
    {
        return $this->hasMany(PayrollAlert::class, 'employee_id', 'employee_id');
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class, 'employee_id', 'employee_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'employee_id', 'employee_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'generated_by', 'employee_id');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class, 'user_id', 'employee_id');
    }

    public function updatedSettings()
    {
        return $this->hasMany(Setting::class, 'updated_by', 'employee_id');
    }
}