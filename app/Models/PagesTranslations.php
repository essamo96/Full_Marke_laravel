<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagesTranslations extends Model
{
    protected $fillable = [
        'page_id',
        'locale',
        'title',
        'subtitle',
        'details',
    ];
}
