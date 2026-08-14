<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAccessLog extends Model
{
    protected $fillable = [
        'subject_resource_id',
        'student_id',
        'session_token',
        'device_id',
        'ip_address',
        'user_agent',
        'last_seen_at',
        'ended_at',
        'expires_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SubjectResource::class, 'subject_resource_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('ended_at')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function isUsable(): bool
    {
        return $this->ended_at === null && ! $this->isExpired();
    }
}
