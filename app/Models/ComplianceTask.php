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

    protected $casts = [
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the employee associated with this compliance task
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'employee_id');
    }
}