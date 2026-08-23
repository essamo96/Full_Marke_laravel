<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalUnit extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['educational_stage_id', 'group_id', 'name_ar', 'name_en', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'educational_stage_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(EducationalLesson::class)->orderBy('sort_order');
    }

    /**
     * Units visible when viewing the given group's content: the shared units
     * (group_id null) plus any units created specifically for that group.
     * With $groupId null (the "shared" tab), only the shared units show.
     */
    public function scopeForGroup($query, ?int $groupId)
    {
        return $query->where(function ($q) use ($groupId) {
            $q->whereNull('group_id');
            if ($groupId) {
                $q->orWhere('group_id', $groupId);
            }
        });
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
