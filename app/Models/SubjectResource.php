<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubjectResource extends Model
{
    protected $table = 'subject_resources';

    protected $fillable = [
        'subject_id', 'educational_lesson_id', 'category', 'title', 'type', 'url', 'description', 'is_active', 'sort_order',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
