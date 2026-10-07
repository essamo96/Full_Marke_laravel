<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The three group pivots (resource / lesson / unit <-> group) had no unique
 * key, so the same link could be stored twice, and a link could only be
 * removed for good.
 *
 *  - de-duplicate, then make (content, group) unique -> "attach" is idempotent
 *    and syncWithoutDetaching() can never create a second row.
 *  - group_subject_resource additionally gets:
 *      is_active   -> per-group kill switch. An *inactive* link is a "pause":
 *                     the resource stays linked but the group stops seeing it,
 *                     and it also overrides is_shared for that group.
 *      deleted_at  -> detaching a group is a soft delete, so it can be undone.
 *      created_by  -> who linked it.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dedupe('group_subject_resource', 'subject_resource_id');
        $this->dedupe('educational_unit_group', 'educational_unit_id');
        $this->dedupe('educational_lesson_group', 'educational_lesson_id');

        Schema::table('group_subject_resource', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('group_id');
            $table->string('created_by_type', 20)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();
            $table->softDeletes();

            $table->unique(['subject_resource_id', 'group_id'], 'group_subject_resource_unique');
        });

        Schema::table('educational_unit_group', function (Blueprint $table) {
            $table->unique(['educational_unit_id', 'group_id'], 'educational_unit_group_unique');
        });

        Schema::table('educational_lesson_group', function (Blueprint $table) {
            $table->unique(['educational_lesson_id', 'group_id'], 'educational_lesson_group_unique');
        });
    }

    public function down(): void
    {
        Schema::table('educational_lesson_group', function (Blueprint $table) {
            $table->dropUnique('educational_lesson_group_unique');
        });

        Schema::table('educational_unit_group', function (Blueprint $table) {
            $table->dropUnique('educational_unit_group_unique');
        });

        Schema::table('group_subject_resource', function (Blueprint $table) {
            $table->dropUnique('group_subject_resource_unique');
            $table->dropColumn(['is_active', 'created_by_type', 'created_by_id', 'deleted_at']);
        });
    }

    /** Keep the oldest row of every (content, group) pair. */
    private function dedupe(string $table, string $contentColumn): void
    {
        DB::statement(
            "DELETE a FROM `{$table}` a
             INNER JOIN `{$table}` b
                ON a.`{$contentColumn}` = b.`{$contentColumn}`
               AND a.`group_id` = b.`group_id`
               AND a.`id` > b.`id`"
        );
    }
};
