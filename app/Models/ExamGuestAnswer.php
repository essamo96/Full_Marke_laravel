<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamGuestAnswer extends Model
{
    protected $fillable = [
        'exam_guest_submission_id', 'exam_id', 'question_id',
        'selected_option_id', 'essay_answer', 'is_correct', 'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(ExamGuestSubmission::class, 'exam_guest_submission_id');
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedOption(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class, 'selected_option_id');
    }
}
