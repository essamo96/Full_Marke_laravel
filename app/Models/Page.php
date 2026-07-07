<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['slug', 'tags', 'image', 'video', 'status'];

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function translations()
    {
        return $this->hasMany(PagesTranslations::class);
    }

    public function translation()
    {
        return $this->hasOne(PagesTranslations::class)->where('locale', app()->getLocale());
    }

    public function getSearch($name, $companies, $emp_id)
    {
        $query = $this->query();

        if ($name) {
            $query->whereHas('translations', function ($q) use ($name) {
                $q->where('title', 'LIKE', '%' . $name . '%');
            });
        }

        return $query->with(['translation']);
    }
}
