<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Shared visibility rules for units / lessons / subject resources.
 *
 * - is_shared = true  → visible in every group of the subject; no pivot rows
 * - is_shared = false + group_ids → visible only in those groups
 * - is_shared = false + no groups → draft (shared-tab only for teachers)
 */
class EducationalContentVisibility
{
    public static function resolveIsShared(Request $request, ?array $groupIds): bool
    {
        // Prefer an explicit is_shared field (checkbox + hidden 0).
        // When both "0" and "1" are posted, take the last boolean-true if any "1" exists.
        if ($request->has('is_shared')) {
            $raw = $request->input('is_shared');
            if (is_array($raw)) {
                return in_array('1', $raw, true) || in_array(1, $raw, true) || in_array(true, $raw, true);
            }

            return filter_var($raw, FILTER_VALIDATE_BOOLEAN);
        }

        // Add forms without the checkbox: empty groups = shared for all.
        return empty($groupIds);
    }

    public static function apply(Model $model, bool $isShared, ?array $groupIds = []): void
    {
        $groupIds = array_values(array_filter(array_map('intval', $groupIds ?? [])));

        $model->forceFill(['is_shared' => $isShared])->save();

        if ($isShared) {
            $model->groups()->sync([]);
        } else {
            $model->groups()->sync($groupIds);
        }
    }

    /**
     * Delete from a single group context without wiping shared/other-group copies.
     * Returns: detached | soft_deleted | deleted | blocked_shared
     */
    public static function destroyForGroup(Model $model, ?int $detachGroupId): string
    {
        if (! $detachGroupId) {
            $model->delete();

            return method_exists($model, 'trashed') && $model->trashed()
                ? 'soft_deleted'
                : 'deleted';
        }

        if ((bool) $model->is_shared) {
            // Shared content has no per-group row to detach — caller should confirm global delete.
            return 'blocked_shared';
        }

        $model->groups()->detach($detachGroupId);

        if ($model->groups()->count() === 0) {
            $model->delete();

            return method_exists($model, 'trashed') && $model->trashed()
                ? 'soft_deleted'
                : 'deleted';
        }

        return 'detached';
    }

    public static function nextSortOrder(Model $model, string $parentForeignKey, int $parentId): int
    {
        $max = $model->newQuery()
            ->where($parentForeignKey, $parentId)
            ->max('sort_order');

        return ((int) $max) + 1;
    }
}
