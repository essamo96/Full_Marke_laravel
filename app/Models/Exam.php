<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptsRouteKey;

class Exam extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = [
        'subject_id',
        'group_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'excluded_student_ids',
        'start_alert_sent_at',
        'audience',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'excluded_student_ids' => 'array',
        'start_alert_sent_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function guestSubmissions()
    {
        return $this->hasMany(ExamGuestSubmission::class);
    }

    public function allowsGuests(): bool
    {
        return in_array($this->audience, ['guests', 'both'], true);
    }

    public function allowsStudents(): bool
    {
        return in_array($this->audience, ['students', 'both'], true);
    }
}
