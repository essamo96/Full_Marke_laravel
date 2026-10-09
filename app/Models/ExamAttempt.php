<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A student's in-progress (or finished) sitting of an online exam: the
 * server-side start time that drives the countdown, plus the autosaved
 * draft answers as { question_id: { v: value, t: client epoch ms } }.
 */
class ExamAttempt extends Model
{
    protected $fillable = ['exam_id', 'student_id', 'started_at', 'answers', 'saved_at', 'submitted_at'];

    protected $casts = [
        'started_at' => 'datetime',
        'answers' => 'array',
        'saved_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
