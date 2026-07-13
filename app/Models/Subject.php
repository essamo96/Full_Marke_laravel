<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id', 'name_en', 'name_ar', 'description_en', 'description_ar',
        'image', 'reg_start_date', 'reg_end_date', 'fee', 'min_payment', 'total_fee',
        'discount_percent', 'google_tags', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'google_tags' => 'array',
        'is_active' => 'boolean',
        'reg_start_date' => 'date',
        'reg_end_date' => 'date',
        'fee' => 'decimal:2',
        'min_payment' => 'decimal:2',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(Group::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(SubjectResource::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->description_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
