<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptsRouteKey;

class SubjectResource extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $table = 'subject_resources';

    protected $fillable = [
        'subject_id', 'educational_lesson_id', 'category', 'title', 'type', 'url', 'description', 'is_active', 'sort_order',
        'processing_status', 'hls_path', 'encryption_key_path', 'duration_seconds', 'original_filename', 'processing_error',
    ];

    protected $casts = [
        'is_active' => 'boolean',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isVideo(): bool
    {
        return $this->type === 'video' && ! preg_match('#^https?://#i', (string) $this->url);
    }

    public function isReady(): bool
    {
        if ($this->isVideo()) {
            return $this->processing_status === 'ready' && ! empty($this->hls_path);
        }

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
