<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAccessLog extends Model
{
    protected $fillable = [
        'subject_resource_id', 'student_id', 'session_token', 'ip_address', 'user_agent', 'last_seen_at', 'ended_at',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
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
        return $query->whereNull('ended_at');
    }
}
