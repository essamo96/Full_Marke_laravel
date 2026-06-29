<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasRoles, Notifiable;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'role',
        'photo',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function confirmedPayments()
    {
        return $this->hasMany(Payment::class, 'confirmed_by');
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
