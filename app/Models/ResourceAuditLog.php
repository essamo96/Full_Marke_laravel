<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * One entry of the resource-library journal. `before` stores a snapshot of every row
 * the action touched, which is what the undo toast replays. See App\Services\ResourceLibrary\LibraryJournal.
 */
class ResourceAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'uuid', 'actor_type', 'actor_id', 'subject_id', 'action',
        'target_type', 'target_id', 'summary', 'before', 'meta',
        'undone_at', 'undone_by_type', 'undone_by_id',
    ];

    protected $casts = [
        'before' => 'array',
        'meta' => 'array',
        'undone_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $log) {
            $log->uuid ??= (string) Str::uuid();
        });
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function isUndone(): bool
    {
        return $this->undone_at !== null;
    }

    public function scopeForSubject($query, int $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }
}
