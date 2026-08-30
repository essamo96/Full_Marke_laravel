<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalUnit extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['educational_stage_id', 'name_ar', 'name_en', 'is_shared', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'educational_stage_id');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'educational_unit_group');
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

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
