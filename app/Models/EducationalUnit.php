<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationalUnit extends Model
{
    protected $fillable = ['educational_stage_id', 'name_ar', 'name_en', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'educational_stage_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(EducationalLesson::class)->orderBy('sort_order');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
