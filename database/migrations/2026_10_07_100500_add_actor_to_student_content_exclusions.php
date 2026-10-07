<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * student_content_exclusions is the exclusion table of the library: one row per
 * (student, resource | lesson | unit). It was polymorphic from the start, which
 * is what lets a teacher exclude a student from a whole unit, not only from a
 * single video. What it lacked was *who* excluded the student - needed so a
 * teacher never overwrites an admin's exclusion and so support can answer
 * "why can't this student see the video?".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_content_exclusions', function (Blueprint $table) {
            $table->string('excluded_by_type', 20)->nullable()->after('reason');
            $table->unsignedBigInteger('excluded_by_id')->nullable()->after('excluded_by_type');

            $table->index(['excluded_by_type', 'excluded_by_id'], 'student_content_exclusions_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::table('student_content_exclusions', function (Blueprint $table) {
            $table->dropIndex('student_content_exclusions_actor_idx');
            $table->dropColumn(['excluded_by_type', 'excluded_by_id']);
        });
    }
};
