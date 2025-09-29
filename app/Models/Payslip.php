<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payslip extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    // Add to Payslip model
    public function allowance()
    {
        return $this->belongsTo(Allowance::class);
    }

    public function deduction()
    {
        return $this->belongsTo(Deduction::class);
    }
}
