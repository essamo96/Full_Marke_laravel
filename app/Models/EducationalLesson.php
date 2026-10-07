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
 * A lesson inside a unit. Like units, `is_shared` + groups() are only the declared scope
 * (listing in group tabs / default audience); a student's view is decided per resource.
 */
class EducationalLesson extends Model
{
    use \App\Traits\EncryptsRouteKey;
    use Concerns\HasEducationalContentVisibility;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'educational_unit_id', 'name_ar', 'name_en', 'is_shared', 'sort_order', 'is_active',
        'deleted_by_type', 'deleted_by_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_shared' => 'boolean',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(EducationalUnit::class, 'educational_unit_id');
    }

    /**
     * Resources shown in this lesson. A resource can be shown in many lessons, so this is
     * many-to-many through placements; the pivot carries the order inside the lesson.
     */
    public function resources(): BelongsToMany
    {
        return $this->belongsToMany(SubjectResource::class, 'resource_placements', 'educational_lesson_id', 'subject_resource_id')
            ->wherePivotNull('deleted_at')
            ->withPivot(['id', 'sort_order', 'is_active'])
            ->orderBy('resource_placements.sort_order');
    }

    public function placements(): HasMany
    {
        return $this->hasMany(ResourcePlacement::class, 'educational_lesson_id');
    }

    /** Declared scope (see class docblock). */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'educational_lesson_group');
    }

    /**
     * Lessons a student sees: open for them and showing at least one resource.
     *
     * @param  int|array<int>|null  $groupIds
     */
    public function scopeVisibleToStudent(Builder $query, int $studentId, int|array|null $groupIds): Builder
    {
        StudentVisibility::constrainLessons(
            $query,
            $this->getTable(),
            $studentId,
            array_values(array_filter((array) $groupIds)),
        );

        return $query;
    }

    /** Lessons to list in a management tab: shared, drafts, or declared for the group. */
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
