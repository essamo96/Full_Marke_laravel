<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class Grade extends Model
{
    use EncryptsRouteKey;

    protected $fillable = [
        'student_id',
        'group_id',
        'exam_id',
        'exam_name',
        'score',
        'max_score',
        'notes',
        'started_at',
        'time_taken_minutes',
        'tab_switch_count',
        'fullscreen_exit_count',
        'auto_submitted',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'auto_submitted' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers()
    {
        return $this->hasMany(ExamAnswer::class);
    }
}
