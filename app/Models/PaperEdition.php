<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaperEdition extends Model
{
    protected $fillable = [
        'title_ar', 'title_en', 'cover_image', 'pdf_file', 'published_date', 'status'
    ];
}
