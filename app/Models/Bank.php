<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'bank_name', 'name');
    }
}