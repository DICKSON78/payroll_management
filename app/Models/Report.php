<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'report_id',
        'type',
        'period',
        'employee_id',
        'export_format',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
