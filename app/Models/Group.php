<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EncryptsRouteKey;

class Group extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'subject_id', 'teacher_id', 'name', 'image', 'days', 'start_time', 'end_time',
        'max_capacity', 'current_count', 'start_date', 'end_date', 'zoom_link', 'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function joinCodes(): HasMany
    {
        return $this->hasMany(GroupJoinCode::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(GroupNote::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function hasAvailableCapacity(): bool
    {
        // Calculate pending registrations + active ones, or just current_count if it handles it.
        // For Phase 3, we count pending and active registrations.
        $reserved = $this->registrations()
                         ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
                         ->count();

        return $reserved < $this->max_capacity;
    }
}
