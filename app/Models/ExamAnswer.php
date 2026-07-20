<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class ExamAnswer extends Model
{
    use EncryptsRouteKey;

    protected $fillable = [
        'grade_id', 'exam_id', 'question_id', 'student_id',
        'selected_option_id', 'essay_answer', 'is_correct', 'points_earned',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_earned' => 'decimal:2',
    ];

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
