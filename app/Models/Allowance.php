<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Allowance extends Model
{
    use SoftDeletes;

    protected $table = 'allowance'; // Match renamed table
    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'taxable' => 'boolean',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_allowance', 'allowance_id', 'employee_id')
                    ->withPivot('id')
                    ->withTimestamps();
    }
}