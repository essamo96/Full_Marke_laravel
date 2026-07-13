<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar', 'name_en', 'slug', 'image', 'type', 
        'short_description', 'long_description', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class)->orderBy('sort_order');
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    public function getTitleArAttribute(): string
    {
        return $this->name_ar;
    }

    public function getTitleEnAttribute(): string
    {
        return $this->name_en;
    }

    public function getDescriptionAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->long_description : $this->short_description;
    }

    public function getDescriptionArAttribute(): ?string
    {
        return $this->long_description;
    }

    public function getDescriptionEnAttribute(): ?string
    {
        return $this->short_description;
    }
}
