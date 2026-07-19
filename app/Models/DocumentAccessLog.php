<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    protected $fillable = [
        'subject_resource_id', 'student_id', 'ip_address', 'user_agent',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(SubjectResource::class, 'subject_resource_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
