<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class GroupNote extends Model
{
    use EncryptsRouteKey;

    protected $fillable = ['group_id', 'teacher_id', 'title', 'content'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
