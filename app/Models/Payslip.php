<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id', 'payroll_id', 'period', 'gross_salary', 'nssf', 'paye', 'nhif',
        'other_deductions', 'net_salary', 'status', 'wcf', 'sdl'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}