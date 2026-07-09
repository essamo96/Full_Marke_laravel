<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'status',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
    
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
