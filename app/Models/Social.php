<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Social extends Model
{
    protected $fillable = [
        'name_ar',
        'name_en',
        'link',
        'icon',
        'image',
        'status',
    ];

    public function getImagePathAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : asset('assets/media/svg/avatars/blank.svg');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
