<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class GroupNote extends Model
{
    use EncryptsRouteKey;

    protected $fillable = ['group_id', 'teacher_id', 'student_id', 'title', 'content', 'is_alert'];

    protected $casts = [
        'is_alert' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
