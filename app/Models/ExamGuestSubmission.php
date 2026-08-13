<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\EncryptsRouteKey;

class ExamGuestSubmission extends Model
{
    use EncryptsRouteKey;

    protected $fillable = [
        'exam_id', 'guest_name', 'guest_phone', 'guest_email',
        'score', 'max_score', 'notes', 'started_at', 'time_taken_minutes',
        'tab_switch_count', 'fullscreen_exit_count', 'auto_submitted',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'auto_submitted' => 'boolean',
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ExamGuestAnswer::class);
    }
}
