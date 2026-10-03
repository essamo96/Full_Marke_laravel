<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentContentGrant extends Model
{
    public const SOURCE_TRANSFER = 'transfer';
    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'student_id',
        'subject_id',
        'grantable_type',
        'grantable_id',
        'source',
        'from_group_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function fromGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'from_group_id');
    }

    public function grantable(): MorphTo
    {
        return $this->morphTo();
    }
}
