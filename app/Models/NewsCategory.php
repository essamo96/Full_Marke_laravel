<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'slug', 'image', 'status'];

    public function articles()
    {
        return $this->hasMany(NewsArticle::class, 'category_id');
    }
}
