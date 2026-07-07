<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsTranslation extends Model
{
    protected $fillable = ['news_id', 'locale', 'title', 'description'];
    
    public $timestamps = true;
}
