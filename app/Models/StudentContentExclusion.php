<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentContentExclusion extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'excludable_type',
        'excludable_id',
        'reason',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function excludable(): MorphTo
    {
        return $this->morphTo();
    }
}
