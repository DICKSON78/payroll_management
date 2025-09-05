<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'tax_rate',
        'payroll_cycle',
        'currency',
        'company_logo',
    ];
}