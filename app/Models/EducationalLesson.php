<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalLesson extends Model
{
    use \App\Traits\EncryptsRouteKey;

    protected $fillable = ['educational_unit_id', 'name_ar', 'name_en', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    public function resources(): HasMany
    {
        return $this->hasMany(SubjectResource::class)->orderBy('sort_order');
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'educational_lesson_group');
    }

    public function scopeForGroup($query, ?int $groupId)
    {
        return $query->where(function ($q) use ($groupId) {
            $q->doesntHave('groups');
            if ($groupId) {
                $q->orWhereHas('groups', function ($q2) use ($groupId) {
                    $q2->where('groups.id', $groupId);
                });
            }
        });
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
