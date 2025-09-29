<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $guarded = [];
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
}