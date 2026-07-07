<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    protected $fillable = ['status'];

    public function translations()
    {
        return $this->hasMany(FaqTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(FaqTranslation::class)->where('locale', app()->getLocale());
    }

    public function getSearch($name, $companies, $emp_id)
    {
        $query = $this->query();

        if ($name) {
            $query->whereHas('translations', function ($q) use ($name) {
                $q->where('question', 'LIKE', '%' . $name . '%');
            });
        }

        return $query->with(['translation']);
    }
}
