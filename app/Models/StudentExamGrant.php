<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentExamGrant extends Model
{
    public const SOURCE_TRANSFER = 'transfer';
    public const SOURCE_MANUAL = 'manual';

    protected $fillable = [
        'student_id',
        'exam_id',
        'subject_id',
        'from_group_id',
        'source',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function fromGroup(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'from_group_id');
    }
}
