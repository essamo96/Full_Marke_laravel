<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id', 'user_type', 'subject_id', 'group_id',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function scopeForUser($query, int $userId, string $userType)
    {
        return $query->where('user_id', $userId)->where('user_type', $userType);
    }
}
