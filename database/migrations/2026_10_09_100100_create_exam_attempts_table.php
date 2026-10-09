<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Server-side state of an in-progress online exam: the authoritative start
     * time (so a reload / reconnect can never reset the countdown) and the
     * autosaved draft answers (so a dropped connection can never lose or flip
     * what the student already picked).
     */
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            // null until the student presses "start": the countdown only runs from then on
            $table->timestamp('started_at')->nullable();
            // { "<question_id>": { "v": <option id | essay text>, "t": <client epoch ms> } }
            $table->json('answers')->nullable();
            $table->timestamp('saved_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};
