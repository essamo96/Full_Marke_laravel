<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Placement = "where in the curriculum a resource is shown".
 *
 * Until now a resource belonged to exactly one lesson through
 * subject_resources.educational_lesson_id, so showing the same video in a second
 * lesson meant uploading it a second time (the dev data is full of such
 * duplicates). A resource can now have many placements:
 *
 *      target_key  meaning
 *      ----------  ---------------------------------------------------
 *      L<id>       inside lesson <id>
 *      U<id>       directly under unit <id> (no lesson)
 *      G           "general": the subject level, outside any unit
 *
 * A resource with no active placement is *unplaced* and is shown to nobody -
 * it never silently falls back to "general".
 *
 * The old subject_resources.educational_lesson_id column is kept (nullable) as
 * the "primary lesson" for legacy readers and for rolling back.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_resource_id')->constrained('subject_resources')->cascadeOnDelete();
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->cascadeOnDelete();
            $table->foreignId('educational_lesson_id')->nullable()->constrained('educational_lessons')->cascadeOnDelete();
            $table->string('target_key', 24);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('created_by_type', 20)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['subject_resource_id', 'target_key'], 'resource_placements_unique_target');
            $table->index(['educational_lesson_id', 'sort_order'], 'resource_placements_lesson_idx');
            $table->index(['educational_unit_id', 'sort_order'], 'resource_placements_unit_idx');
        });

        $lessonIds = DB::table('educational_lessons')->pluck('id')->flip();
        $now = now();

        // Includes trashed resources on purpose, so restoring one later brings
        // it back to the same place.
        DB::table('subject_resources')->orderBy('id')->chunkById(200, function ($resources) use ($lessonIds, $now) {
            $rows = [];

            foreach ($resources as $resource) {
                $lessonId = $resource->educational_lesson_id;
                $isLesson = $lessonId && isset($lessonIds[$lessonId]);

                $rows[] = [
                    'subject_resource_id' => $resource->id,
                    'educational_unit_id' => null,
                    'educational_lesson_id' => $isLesson ? $lessonId : null,
                    'target_key' => $isLesson ? 'L'.$lessonId : 'G',
                    'sort_order' => (int) $resource->sort_order,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows) {
                DB::table('resource_placements')->insert($rows);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_placements');
    }
};
