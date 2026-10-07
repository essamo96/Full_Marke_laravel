<?php

namespace App\Support\Library;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\ResourcePlacement;
use App\Models\SubjectResource;

/**
 * The student-side visibility rules, written once as SQL.
 *
 * Every student-facing question - "which videos are in my list", "may I stream this
 * file", "which lessons are empty and should be hidden" - is answered by these
 * clauses, so a listing page and a direct URL can never disagree.
 *
 * A resource is visible to student S (whose groups in the subject are G...) when ALL hold:
 *
 *  1. KILL SWITCH   the resource is active and not deleted
 *  2. EXCLUSION     there is no exclusion row for S on the resource
 *  3. AUDIENCE      one of:
 *                     - it is shared with every group AND not paused for all of S's groups
 *                     - it has an active link to one of S's groups
 *                     - S holds a personal grant (kept after a group transfer)
 *  4. PLACEMENT     it has at least one active placement whose whole path is open:
 *                     general                      -> always open
 *                     unit  U                      -> U active, stage active, S not excluded from U
 *                     lesson L (in unit U)         -> L and U active, stage active, S excluded from neither
 *
 * A resource nobody placed anywhere is visible to nobody. Group lists on units and
 * lessons are NOT consulted: the resource's own audience is the single source of truth.
 */
final class StudentVisibility
{
    /**
     * Constrain a query over subject_resources (Eloquent or query builder).
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int>  $groupIds  the student's group(s) in the resource's subject
     * @param  bool  $reachable  false = skip rule 4 (used when the caller already is inside a container)
     */
    public static function constrainResources($query, string $alias, int $studentId, array $groupIds, bool $reachable = true): void
    {
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));
        $resourceType = (new SubjectResource)->getMorphClass();

        // 1. kill switch / soft delete
        $query->where("{$alias}.is_active", true)->whereNull("{$alias}.deleted_at");

        // 2. per-student exclusion on the resource itself
        $query->whereNotExists(fn ($sub) => $sub->selectRaw('1')
            ->from('student_content_exclusions as rex')
            ->whereColumn('rex.excludable_id', "{$alias}.id")
            ->where('rex.excludable_type', $resourceType)
            ->where('rex.student_id', $studentId));

        // 3. audience
        $query->where(function ($audience) use ($alias, $studentId, $groupIds, $resourceType) {
            $audience->where(function ($shared) use ($alias, $groupIds) {
                $shared->where("{$alias}.is_shared", true);

                if ($groupIds) {
                    // "paused for a group" overrides sharing; the student still sees the
                    // resource if at least one of their groups is NOT paused.
                    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
                    $shared->whereRaw(
                        "(select count(*) from group_subject_resource sp
                           where sp.subject_resource_id = {$alias}.id
                             and sp.deleted_at is null and sp.is_active = 0
                             and sp.group_id in ({$placeholders})) < ?",
                        [...$groupIds, count($groupIds)]
                    );
                }
            });

            if ($groupIds) {
                $audience->orWhereExists(fn ($sub) => $sub->selectRaw('1')
                    ->from('group_subject_resource as gl')
                    ->whereColumn('gl.subject_resource_id', "{$alias}.id")
                    ->whereNull('gl.deleted_at')
                    ->where('gl.is_active', true)
                    ->whereIn('gl.group_id', $groupIds));
            }

            $audience->orWhereExists(fn ($sub) => $sub->selectRaw('1')
                ->from('student_content_grants as sg')
                ->whereColumn('sg.grantable_id', "{$alias}.id")
                ->where('sg.grantable_type', $resourceType)
                ->where('sg.student_id', $studentId));
        });

        // 4. placement
        if ($reachable) {
            $query->whereExists(fn ($sub) => $sub->selectRaw('1')
                ->from('resource_placements as rp')
                ->whereColumn('rp.subject_resource_id', "{$alias}.id")
                ->whereNull('rp.deleted_at')
                ->where('rp.is_active', true)
                ->where(function ($path) use ($studentId) {
                    $path->where('rp.target_key', ResourcePlacement::GENERAL)
                        ->orWhere(fn ($unitLevel) => $unitLevel
                            ->whereNull('rp.educational_lesson_id')
                            ->whereNotNull('rp.educational_unit_id')
                            ->whereExists(fn ($u) => self::openUnit($u, 'rp.educational_unit_id', $studentId)))
                        ->orWhere(fn ($lessonLevel) => $lessonLevel
                            ->whereNotNull('rp.educational_lesson_id')
                            ->whereExists(fn ($l) => self::openLesson($l, 'rp.educational_lesson_id', $studentId)));
                }));
        }
    }

    /** Unit `uu` exists, is active, its stage is active and the student is not excluded from it. */
    private static function openUnit($query, string $unitIdColumn, int $studentId): void
    {
        $query->selectRaw('1')
            ->from('educational_units as uu')
            ->join('educational_stages as ust', 'ust.id', '=', 'uu.educational_stage_id')
            ->whereColumn('uu.id', $unitIdColumn)
            ->whereNull('uu.deleted_at')
            ->where('uu.is_active', true)
            ->where('ust.is_active', true)
            ->whereNotExists(fn ($e) => $e->selectRaw('1')
                ->from('student_content_exclusions as uex')
                ->whereColumn('uex.excludable_id', 'uu.id')
                ->where('uex.excludable_type', (new EducationalUnit)->getMorphClass())
                ->where('uex.student_id', $studentId));
    }

    /** Lesson `ll` is active and its unit is open and the student is excluded from neither. */
    private static function openLesson($query, string $lessonIdColumn, int $studentId): void
    {
        $query->selectRaw('1')
            ->from('educational_lessons as ll')
            ->whereColumn('ll.id', $lessonIdColumn)
            ->whereNull('ll.deleted_at')
            ->where('ll.is_active', true)
            ->whereNotExists(fn ($e) => $e->selectRaw('1')
                ->from('student_content_exclusions as lex')
                ->whereColumn('lex.excludable_id', 'll.id')
                ->where('lex.excludable_type', (new EducationalLesson)->getMorphClass())
                ->where('lex.student_id', $studentId))
            ->whereExists(fn ($u) => self::openUnit($u, 'll.educational_unit_id', $studentId));
    }

    /**
     * Constrain a query over educational_lessons: the lesson is open for the student AND
     * shows at least one resource to them.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int>  $groupIds
     */
    public static function constrainLessons($query, string $alias, int $studentId, array $groupIds): void
    {
        $query->whereExists(fn ($l) => self::openLesson($l, "{$alias}.id", $studentId));

        $query->whereExists(fn ($sub) => self::resourcesInside($sub, $studentId, $groupIds)
            ->whereColumn('rp.educational_lesson_id', "{$alias}.id"));
    }

    /**
     * Constrain a query over educational_units: the unit is open for the student AND
     * shows at least one resource to them, directly or through one of its open lessons.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder  $query
     * @param  array<int>  $groupIds
     */
    public static function constrainUnits($query, string $alias, int $studentId, array $groupIds): void
    {
        $query->whereExists(fn ($u) => self::openUnit($u, "{$alias}.id", $studentId));

        $query->where(function ($has) use ($alias, $studentId, $groupIds) {
            $has->whereExists(fn ($sub) => self::resourcesInside($sub, $studentId, $groupIds)
                ->whereColumn('rp.educational_unit_id', "{$alias}.id"))
                ->orWhereExists(fn ($sub) => self::resourcesInside($sub, $studentId, $groupIds)
                    ->join('educational_lessons as pl', 'pl.id', '=', 'rp.educational_lesson_id')
                    ->whereColumn('pl.educational_unit_id', "{$alias}.id")
                    ->whereNull('pl.deleted_at')
                    ->where('pl.is_active', true)
                    ->whereNotExists(fn ($e) => $e->selectRaw('1')
                        ->from('student_content_exclusions as plx')
                        ->whereColumn('plx.excludable_id', 'pl.id')
                        ->where('plx.excludable_type', (new EducationalLesson)->getMorphClass())
                        ->where('plx.student_id', $studentId)));
        });
    }

    /**
     * placements rp -> resources vr that pass rules 1-3 (the container check is the caller's job).
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private static function resourcesInside($sub, int $studentId, array $groupIds)
    {
        $sub->selectRaw('1')
            ->from('resource_placements as rp')
            ->join('subject_resources as vr', 'vr.id', '=', 'rp.subject_resource_id')
            ->whereNull('rp.deleted_at')
            ->where('rp.is_active', true);

        self::constrainResources($sub, 'vr', $studentId, $groupIds, reachable: false);

        return $sub;
    }
}
