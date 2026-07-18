<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsTag extends Model
{
    protected $fillable = ['name_ar', 'name_en', 'slug'];

    public function articles()
    {
        return $this->belongsToMany(NewsArticle::class, 'article_tag', 'tag_id', 'article_id');
    }
}
