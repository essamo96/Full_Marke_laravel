<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['image', 'display_order', 'status'];

    public function translations()
    {
        return $this->hasMany(TestimonialTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(TestimonialTranslation::class)->where('locale', app()->getLocale());
    }

    public function getSearch($name)
    {
        $query = $this->query();

        if ($name) {
            $query->whereHas('translations', function ($q) use ($name) {
                $q->where('name', 'LIKE', '%' . $name . '%');
            });
        }

        return $query->with(['translation'])->orderByDesc('display_order');
    }
}
