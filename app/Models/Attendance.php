<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\EncryptsRouteKey;

class Attendance extends Model
{
    use HasFactory, EncryptsRouteKey;

    protected $fillable = ['student_id', 'group_id', 'teacher_id', 'date', 'status', 'notes'];

    protected $casts = [
        'date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
