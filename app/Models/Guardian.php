<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Guardian extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name', 'national_id', 'phone', 'email', 'password',
        'address', 'relationship', 'photo', 'is_active', 'email_verified_at',
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
            'is_active' => 'boolean',
        ];
    }

    public function children(): HasMany
    {
        return $this->hasMany(Student::class, 'guardian_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
