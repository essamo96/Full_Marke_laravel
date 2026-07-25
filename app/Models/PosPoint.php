<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_ar', 'name_en', 'image', 'address_ar', 'address_en',
        'working_hours_ar', 'working_hours_en', 'booklet_price', 'phone',
        'latitude', 'longitude', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'booklet_price' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getAddressAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->address_ar : $this->address_en;
    }

    public function getWorkingHoursAttribute(): ?string
    {
        return app()->getLocale() === 'ar' ? $this->working_hours_ar : $this->working_hours_en;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
