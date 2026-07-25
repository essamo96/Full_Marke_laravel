<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Student extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name_ar', 'full_name_en', 'national_id', 'is_child', 'guardian_id',
        'phone', 'email', 'image', 'date_of_birth', 'gender', 'address',
        'region_id', 'branch_id', 'major_profession', 'health_information',
        'status', 'email_verified_at', 'password', 'parent_id', 'study_branch_id'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'password' => 'hashed',
        'status' => 'boolean',
        'is_child' => 'boolean',
    ];

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'guardian_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }


    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->full_name_ar : $this->full_name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    /**
     * Total remaining fees across all this student's registrations (fee_snapshot - amount_paid).
     */
    public function getTotalDueAttribute(): float
    {
        return $this->registrations->sum(fn (Registration $r) => $r->remaining_amount);
    }

    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->isEmailVerified();
    }
}
