<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'subject_id', 'teacher_id', 'name', 'days', 'start_time', 'end_time',
        'max_capacity', 'current_count', 'start_date', 'end_date', 'zoom_link', 'is_active',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

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

    public function hasAvailableCapacity(): bool
    {
        return $this->current_count < $this->max_capacity;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
