<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\EncryptsRouteKey;

class SubjectResource extends Model
{
    use HasFactory, SoftDeletes, EncryptsRouteKey;

    protected $table = 'subject_resources';
    protected $fillable = [
        'subject_id', 'educational_lesson_id', 'category', 'title', 'type', 'url', 'description', 'is_active', 'sort_order',
        'processing_status', 'hls_path', 'encryption_key_path', 'duration_seconds', 'original_filename', 'processing_error',
        'allow_download', 'is_shared',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(EducationalLesson::class, 'educational_lesson_id');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(VideoAccessLog::class);
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'deleted_by');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_subject_resource');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGroup($query, $groupId)
    {
        return $query->where(function ($q) use ($groupId) {
            if ($groupId) {
                // Student View: Shared items OR items specific to this group
                $q->where('is_shared', true)
                  ->orWhereHas('groups', function ($q2) use ($groupId) {
                      $q2->where('groups.id', $groupId);
                  });
            } else {
                // Admin/Teacher Shared View: Shared items OR Drafts (0 groups)
                $q->where('is_shared', true)
                  ->orDoesntHave('groups');
            }
        });
    }

    public function isVideo(): bool
    {
        return $this->type === 'video' && ! preg_match('#^https?://#i', (string) $this->url);
    }

    public function isReady(): bool
    {
        return $this->processing_status === 'ready';
    }

    public function isExternalLink(): bool
    {
        return (bool) preg_match('#^https?://#i', (string) $this->url);
    }

    public function isImage(): bool
    {
        return $this->type === 'image' && ! $this->isExternalLink();
    }
}
