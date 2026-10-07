<?php

namespace App\Models;

use App\Support\Library\StudentVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A unit of the curriculum (stage > unit > lesson > resources).
 *
 * `is_shared` + groups() are the unit's DECLARED scope: they decide in which group tab
 * the unit is listed while it is still empty, and which audience new content gets by
 * default. They never hide anything from a student on their own - what a student sees is
 * decided by the audience of each resource (see StudentVisibility).
 */
class EducationalUnit extends Model
{
    use \App\Traits\EncryptsRouteKey;
    use Concerns\HasEducationalContentVisibility;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'educational_stage_id', 'name_ar', 'name_en', 'is_shared', 'sort_order', 'is_active',
        'deleted_by_type', 'deleted_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function stage(): BelongsTo
    {
        return $this->belongsTo(EducationalStage::class, 'educational_stage_id');
    }

    /** Declared scope (see class docblock). */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'educational_unit_group');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(EducationalLesson::class)->orderBy('sort_order');
    }

    /** Resources placed directly under the unit, without a lesson. */
    public function directResources(): BelongsToMany
    {
        return $this->belongsToMany(SubjectResource::class, 'resource_placements', 'educational_unit_id', 'subject_resource_id')
            ->wherePivotNull('deleted_at')
            ->withPivot(['id', 'sort_order', 'is_active'])
            ->orderBy('resource_placements.sort_order');
    }

    public function placements(): HasMany
    {
        return $this->hasMany(ResourcePlacement::class, 'educational_unit_id');
    }

    /**
     * Units a student sees: open for them and showing at least one resource.
     * Derived from the resource gate - an empty unit is never listed to students.
     *
     * @param  int|array<int>|null  $groupIds
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId, int|array|null $groupIds): Builder
    {
        StudentVisibility::constrainUnits(
            $query,
            $this->getTable(),
            $studentId,
            array_values(array_filter((array) $groupIds)),
        );

        return $query;
    }

    /**
     * Units to list in a management tab: shared units, drafts, and units declared for the group.
     * (Units that merely contain resources of the group are added by the library service.)
     */
    public function scopeDeclaredFor(Builder $query, ?int $groupId): Builder
    {
        return $query->where(function (Builder $q) use ($groupId) {
            $q->where('is_shared', true);

            if ($groupId) {
                $q->orWhereHas('groups', fn ($g) => $g->where('groups.id', $groupId));
            } else {
                $q->orDoesntHave('groups');
            }
        });
    }

    public function getNameAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->name_ar : ($this->name_en ?: $this->name_ar);
    }
}
