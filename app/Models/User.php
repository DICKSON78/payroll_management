<?php

// App\Models\User.php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Role;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',

    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationship to Role (optional)
     */
    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Get the user's role for middleware convenience
     */
    public function getRoleAttribute()
    {
        // Kama role ipo, tumia hiyo, vinginevyo angalia relation
        return $this->role ?? $this->roleRelation?->slug ?? null;
    }
}


?>