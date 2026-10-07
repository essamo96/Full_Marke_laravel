<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('educational_units', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('name_en');
        });
        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('name_en');
        });
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('allow_download');
        });

        // Data preservation logic.
        //
        // Plain query builder on purpose: the Eloquent models keep evolving
        // (soft deletes, pivot columns...) and this migration runs *before* those
        // columns exist on a fresh install, so it must not depend on the models.
        DB::table('educational_units')
            ->whereNotExists(fn ($q) => $q->from('educational_unit_group')
                ->whereColumn('educational_unit_group.educational_unit_id', 'educational_units.id'))
            ->update(['is_shared' => true]);

        DB::table('educational_lessons')
            ->whereNotExists(fn ($q) => $q->from('educational_lesson_group')
                ->whereColumn('educational_lesson_group.educational_lesson_id', 'educational_lessons.id'))
            ->update(['is_shared' => true]);

        DB::table('subject_resources')
            ->whereNull('deleted_at')
            ->whereNotExists(fn ($q) => $q->from('group_subject_resource')
                ->whereColumn('group_subject_resource.subject_resource_id', 'subject_resources.id'))
            ->update(['is_shared' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educational_units', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
    }
};
