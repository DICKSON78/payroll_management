<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    //
    protected $fillable = [
        'id', 'name', 'email', 'department', 'position', 'salary', 'status',
        'gender', 'dob', 'nationality', 'phone', 'address', 'hire_date',
        'base_salary', 'allowances', 'deductions', 'bank_name', 'account_number'
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}
