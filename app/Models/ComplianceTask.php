<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplianceTask extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'task_id',
        'type',
        'employee_id',
        'due_date',
        'amount',
        'details',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     * Hapa tunaeleza 'due_date' ibadilishwe kuwa tarehe kiotomatiki.
     *
     * @var array
     */
    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Get the employee that owns the compliance task.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
