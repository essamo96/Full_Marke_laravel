<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = ['image', 'status'];

    public function translations()
    {
        return $this->hasMany(NewsTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(NewsTranslation::class)->where('locale', app()->getLocale());
    }

    public function getSearch($name, $status = null)
    {
        $query = $this->query();

        if ($name) {
            $query->whereHas('translations', function ($q) use ($name) {
                $q->where('title', 'LIKE', '%' . $name . '%')
                  ->orWhere('description', 'LIKE', '%' . $name . '%');
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('news.status', $status);
        }

        return $query->with(['translation'])->latest();
    }
}
