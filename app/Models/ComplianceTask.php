<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'type', 'employee_id', 'due_date', 'amount', 'details', 'status'
    ];

    protected $dates = ['due_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}