<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('educational_units', function (Blueprint $table) {
            $table->dropForeign(['educational_lesson_id']);
            $table->dropColumn('educational_lesson_id');
            $table->foreignId('educational_stage_id')->nullable()->constrained('educational_stages')->cascadeOnDelete();
        });

        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->dropForeign(['educational_stage_id']);
            $table->dropColumn('educational_stage_id');
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->cascadeOnDelete();
        });

        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropForeign(['educational_unit_id']);
            $table->dropColumn('educational_unit_id');
            $table->foreignId('educational_lesson_id')->nullable()->constrained('educational_lessons')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropForeign(['educational_lesson_id']);
            $table->dropColumn('educational_lesson_id');
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->nullOnDelete();
        });

        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->dropForeign(['educational_unit_id']);
            $table->dropColumn('educational_unit_id');
            $table->foreignId('educational_stage_id')->nullable()->constrained('educational_stages')->cascadeOnDelete();
        });

        Schema::table('educational_units', function (Blueprint $table) {
            $table->dropForeign(['educational_stage_id']);
            $table->dropColumn('educational_stage_id');
            $table->foreignId('educational_lesson_id')->nullable()->constrained('educational_lessons')->cascadeOnDelete();
        });
    }
};
