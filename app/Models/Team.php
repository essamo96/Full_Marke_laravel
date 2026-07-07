<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['image', 'socials', 'member_type', 'is_chairman', 'display_order', 'status'];

    public function translations()
    {
        return $this->hasMany(TeamTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(TeamTranslation::class)->where('locale', app()->getLocale());
    }

    public function getSearch($name, $companies, $emp_id)
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
