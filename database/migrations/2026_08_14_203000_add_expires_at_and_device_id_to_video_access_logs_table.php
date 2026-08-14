<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_access_logs', function (Blueprint $table) {
            // Playback token dies after (video duration + grace). Copied URLs
            // stop working even if the student session is still open.
            $table->timestamp('expires_at')->nullable()->after('ended_at');
            // Bound to the student device-lock cookie so a stream URL lifted
            // from one allowed device cannot be replayed on another.
            $table->string('device_id', 64)->nullable()->after('session_token');

            $table->index(['student_id', 'device_id', 'ended_at']);
            $table->index(['expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('video_access_logs', function (Blueprint $table) {
            $table->dropIndex(['student_id', 'device_id', 'ended_at']);
            $table->dropIndex(['expires_at']);
            $table->dropColumn(['expires_at', 'device_id']);
        });
    }
};
