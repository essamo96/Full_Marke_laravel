<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'group_id',
        'exam_id',
        'exam_name',
        'score',
        'max_score',
        'notes',
        'started_at',
        'time_taken_minutes'
    ];

    protected $casts = [
        'started_at' => 'datetime',
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
}
