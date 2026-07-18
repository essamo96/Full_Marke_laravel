<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsArticle extends Model
{
    protected $fillable = [
        'title_ar', 'title_en', 'content_ar', 'content_en',
        'image', 'category_id', 'admin_id', 'status', 'views', 'published_at'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function tags()
    {
        return $this->belongsToMany(NewsTag::class, 'article_tag', 'article_id', 'tag_id');
    }
}
