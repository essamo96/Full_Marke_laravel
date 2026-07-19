<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_resource_id')->constrained('subject_resources')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_access_logs');
    }
};
