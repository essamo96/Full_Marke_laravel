<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Application extends Model
{
    protected $fillable = [
        'full_name_en', 'full_name_ar', 'email', 'phone', 'image',
        'date_of_birth', 'gender', 'address', 'branch_id', 'study_branch_id',
        'major_profession', 'health_information',
        'program_id', 'subject_id', 'message', 'status',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function studyBranch(): BelongsTo
    {
        return $this->belongsTo(StudyBranch::class);
    }
}
