<?php

namespace App\Models\Concerns;

use App\Models\StudentContentExclusion;
use App\Models\StudentContentGrant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

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

    /**
     * Content visible to a student in their current group context,
     * including personal grants retained after a group transfer,
     * minus explicit per-student exclusions (exclusions always win).
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId, ?int $groupId): Builder
    {
        return $query
            ->where(function (Builder $q) use ($studentId, $groupId) {
                if ($groupId) {
                    $q->where(function (Builder $gq) use ($groupId) {
                        $gq->where('is_shared', true)
                            ->orWhereHas('groups', function ($q2) use ($groupId) {
                                $q2->where('groups.id', $groupId);
                            });
                    });
                } else {
                    // No group assigned: only fully shared subject content.
                    $q->where('is_shared', true);
                }

                $q->orWhereHas('contentGrants', function ($gq) use ($studentId) {
                    $gq->where('student_id', $studentId);
                });
            })
            ->whereDoesntHave('contentExclusions', function ($eq) use ($studentId) {
                $eq->where('student_id', $studentId);
            });
    }

    public function isVisibleToStudent(int $studentId, ?int $groupId): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->visibleToStudent($studentId, $groupId)
            ->exists();
    }

    public function isExcludedForStudent(int $studentId): bool
    {
        return $this->contentExclusions()
            ->where('student_id', $studentId)
            ->exists();
    }
}
