<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_ar', 'name_en', 'details', 'credentials', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credentials' => 'array',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
