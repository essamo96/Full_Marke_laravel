<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Behaviour-preserving data migration for the new visibility model.
 *
 * OLD model: a student saw a resource only if the resource AND its lesson AND
 * its unit each allowed the student's group ("shared" or linked to the group).
 * A child therefore silently inherited the *narrowest* audience of its parents,
 * and widening a child past its parent produced content nobody could see - the
 * root cause of "I shared the video with another group and nothing happens".
 *
 * NEW model: the resource's own audience (is_shared + active group links) is the
 * single source of truth. Units/lessons only structure the tree; they hide a
 * resource through their kill switch or a student exclusion, never through a
 * group list of their own.
 *
 * To keep what every group sees EXACTLY as it is today, this migration copies
 * the old *effective* audience (resource ∩ lesson ∩ unit) onto the resource.
 * Resources that were already open to all groups all the way up stay "shared".
 * A backup table lets down() undo it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_audience_backups', function (Blueprint $table) {
            $table->unsignedBigInteger('subject_resource_id')->primary();
            $table->boolean('was_shared');
            $table->json('was_group_ids');
            $table->timestamp('created_at')->nullable();
        });

        $groupsBySubject = DB::table('groups')->get(['id', 'subject_id'])
            ->groupBy('subject_id')
            ->map(fn ($rows) => $rows->pluck('id')->map(fn ($id) => (int) $id)->all());

        $stages = DB::table('educational_stages')->get(['id', 'subject_id'])->keyBy('id');
        $units = DB::table('educational_units')->get(['id', 'educational_stage_id', 'is_shared'])->keyBy('id');
        $lessons = DB::table('educational_lessons')->get(['id', 'educational_unit_id', 'is_shared'])->keyBy('id');

        $unitGroups = $this->groupIdsBy('educational_unit_group', 'educational_unit_id');
        $lessonGroups = $this->groupIdsBy('educational_lesson_group', 'educational_lesson_id');
        $resourceGroups = $this->groupIdsBy('group_subject_resource', 'subject_resource_id', activeOnly: true);

        $now = now();

        DB::table('subject_resources')->orderBy('id')->chunkById(200, function ($resources) use (
            $groupsBySubject, $stages, $units, $lessons, $unitGroups, $lessonGroups, $resourceGroups, $now
        ) {
            foreach ($resources as $resource) {
                $subjectGroups = $groupsBySubject[$resource->subject_id] ?? [];

                // null === "all groups of the subject"
                $audience = $resource->is_shared ? null : ($resourceGroups[$resource->id] ?? []);

                if ($resource->educational_lesson_id) {
                    $lesson = $lessons[$resource->educational_lesson_id] ?? null;
                    $unit = $lesson ? ($units[$lesson->educational_unit_id] ?? null) : null;
                    $stage = $unit ? ($stages[$unit->educational_stage_id] ?? null) : null;

                    if (! $lesson || ! $unit || ! $stage || (int) $stage->subject_id !== (int) $resource->subject_id) {
                        // Never reachable from any student page before -> keep it hidden.
                        $audience = [];
                    } else {
                        $audience = $this->intersect($audience, $lesson->is_shared ? null : ($lessonGroups[$lesson->id] ?? []));
                        $audience = $this->intersect($audience, $unit->is_shared ? null : ($unitGroups[$unit->id] ?? []));
                    }
                }

                $currentLinks = $resourceGroups[$resource->id] ?? [];

                if ($audience === null) {
                    // Open to everyone all the way up: stays shared, stale links are dropped.
                    $target = ['shared' => true, 'groups' => []];
                } else {
                    $target = [
                        'shared' => false,
                        'groups' => array_values(array_intersect($audience, $subjectGroups)),
                    ];
                }

                sort($currentLinks);
                $targetGroups = $target['groups'];
                sort($targetGroups);

                if ((bool) $resource->is_shared === $target['shared'] && $currentLinks === $targetGroups) {
                    continue;
                }

                DB::table('library_audience_backups')->insert([
                    'subject_resource_id' => $resource->id,
                    'was_shared' => (bool) $resource->is_shared,
                    'was_group_ids' => json_encode($currentLinks),
                    'created_at' => $now,
                ]);

                DB::table('subject_resources')->where('id', $resource->id)->update(['is_shared' => $target['shared']]);

                DB::table('group_subject_resource')
                    ->where('subject_resource_id', $resource->id)
                    ->when($targetGroups, fn ($q) => $q->whereNotIn('group_id', $targetGroups))
                    ->delete();

                $missing = array_diff($targetGroups, $currentLinks);
                if ($missing) {
                    DB::table('group_subject_resource')->insert(array_map(fn ($groupId) => [
                        'subject_resource_id' => $resource->id,
                        'group_id' => $groupId,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ], array_values($missing)));
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('library_audience_backups')) {
            return;
        }

        $now = now();

        DB::table('library_audience_backups')->orderBy('subject_resource_id')->get()->each(function ($backup) use ($now) {
            DB::table('subject_resources')
                ->where('id', $backup->subject_resource_id)
                ->update(['is_shared' => $backup->was_shared]);

            DB::table('group_subject_resource')->where('subject_resource_id', $backup->subject_resource_id)->delete();

            $groups = json_decode($backup->was_group_ids, true) ?: [];
            if ($groups) {
                DB::table('group_subject_resource')->insert(array_map(fn ($groupId) => [
                    'subject_resource_id' => $backup->subject_resource_id,
                    'group_id' => $groupId,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ], $groups));
            }
        });

        Schema::dropIfExists('library_audience_backups');
    }

    /**
     * @return array<int, array<int>>  content id => group ids
     */
    private function groupIdsBy(string $table, string $contentColumn, bool $activeOnly = false): array
    {
        $query = DB::table($table);

        if ($activeOnly) {
            $query->whereNull('deleted_at')->where('is_active', true);
        }

        return $query->get([$contentColumn, 'group_id'])
            ->groupBy($contentColumn)
            ->map(fn ($rows) => $rows->pluck('group_id')->map(fn ($id) => (int) $id)->unique()->values()->all())
            ->all();
    }

    /**
     * Intersection of two audiences where null means "every group".
     *
     * @param  array<int>|null  $a
     * @param  array<int>|null  $b
     * @return array<int>|null
     */
    private function intersect(?array $a, ?array $b): ?array
    {
        if ($a === null) {
            return $b;
        }
        if ($b === null) {
            return $a;
        }

        return array_values(array_intersect($a, $b));
    }
};
