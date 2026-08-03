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
        Schema::table('students', function (Blueprint $table) {
            // Set to now() whenever an admin clears a student's locked
            // device. Any session started before this moment gets signed out
            // on its very next request (EnforceStudentDeviceLock), even
            // though locked_device_id is now null and would otherwise let it
            // through — this is the reliable fallback for when the
            // real-time broadcast (StudentForceLogoutNotification) doesn't
            // reach an already-open tab (offline, blocked websocket, etc).
            $table->timestamp('force_logout_after')->nullable()->after('locked_device_id_set_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('force_logout_after');
        });
    }
};
