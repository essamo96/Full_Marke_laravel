<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * "This student must not see this content" - the resource_exclusions table of the library.
 *
 * Polymorphic on purpose: the target is a resource, a lesson or a whole unit
 * (excluding a student from a unit hides everything inside it). An exclusion always
 * wins over group sharing, "shared with all" and personal grants.
 *
 * excluded_by records who set it, so a teacher can never silently overwrite an
 * exclusion an admin put on one of the teacher's students.
 */
class StudentContentExclusion extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'excludable_type',
        'excludable_id',
        'reason',
        'excluded_by_type',
        'excluded_by_id',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function excludable(): MorphTo
    {
        return $this->morphTo();
    }
}
