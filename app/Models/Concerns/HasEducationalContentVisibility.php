<?php

namespace App\Models\Concerns;

use App\Models\StudentContentExclusion;
use App\Models\StudentContentGrant;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Per-student overrides shared by units, lessons and resources.
 *
 * Who may see a piece of content is decided in ONE place - App\Services\StudentContentGate
 * (resources) and the `visibleToStudent` scope of each model, which is derived from it.
 * This trait only owns the two per-student override tables:
 *
 *  - exclusions: "this student must NOT see it". Always wins, at every level of the
 *    tree (excluding a student from a unit hides everything inside the unit).
 *  - grants:     "this student keeps seeing it" after a group transfer (resources only).
 */
trait HasEducationalContentVisibility
{
    public function contentGrants(): MorphMany
    {
        return $this->morphMany(StudentContentGrant::class, 'grantable');
    }

    public function contentExclusions(): MorphMany
    {
        return $this->morphMany(StudentContentExclusion::class, 'excludable');
    }

    public function isExcludedForStudent(int $studentId): bool
    {
        return $this->contentExclusions()
            ->where('student_id', $studentId)
            ->exists();
    }

    /**
     * @param  int|array<int>|null  $groupIds  the student's group(s) in this subject
     */
    public function isVisibleToStudent(int $studentId, int|array|null $groupIds): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->visibleToStudent($studentId, $groupIds)
            ->exists();
    }
}
