<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Teacher extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'photo', 'phone', 'email_verified_at', 'password', 'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function subjects(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }
}
