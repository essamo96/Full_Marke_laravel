<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_content_exclusions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->morphs('excludable');
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'excludable_type', 'excludable_id'],
                'student_content_exclusions_unique'
            );
            $table->index(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_content_exclusions');
    }
};
