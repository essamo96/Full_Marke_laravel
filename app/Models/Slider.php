<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'title_ar',
        'title_en',
        'desc_ar',
        'desc_en',
        'btn1_text_ar',
        'btn1_text_en',
        'btn1_link',
        'btn2_text_ar',
        'btn2_text_en',
        'btn2_link',
        'image',
        'video1',
        'video2',
        'sort',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
