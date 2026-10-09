<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One exam can now target several groups at once. exams.group_id stays as
     * the "primary" group (first selected) so every legacy query / FK keeps
     * working; the pivot is the full list of target groups.
     */
    public function up(): void
    {
        Schema::create('exam_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['exam_id', 'group_id']);
            $table->index('group_id');
        });

        // Backfill: every existing exam targets exactly its current group.
        $now = now();
        DB::table('exams')->select('id', 'group_id')->orderBy('id')->chunk(500, function ($exams) use ($now) {
            DB::table('exam_group')->insertOrIgnore(
                $exams->map(fn ($e) => [
                    'exam_id' => $e->id,
                    'group_id' => $e->group_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_group');
    }
};
