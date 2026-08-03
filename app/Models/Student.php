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
        'status', 'email_verified_at', 'password', 'parent_id', 'study_branch_id',
        'locked_ip', 'locked_ip_set_at', 'last_seen_at',
        'locked_device_id', 'locked_device_id_set_at', 'force_logout_after',
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
        'locked_ip_set_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'locked_device_id_set_at' => 'datetime',
        'force_logout_after' => 'datetime',
    ];

    /**
     * Whether this student currently shows as "online" for the admin's live
     * active-students panel — a heartbeat within the last 5 minutes.
     */
    public function getIsOnlineAttribute(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gt(now()->subMinutes(5));
    }

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

    /**
     * Public URL of the student's photo, or null when they have none.
     * Images live either under the public disk ("storage/...") or directly
     * in public/ ("site/..."), depending on where they were uploaded from.
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        return str_starts_with($this->image, 'site/')
            ? asset($this->image)
            : asset('storage/' . $this->image);
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
