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
            // The single device a student is currently allowed to log in
            // from. Set on first login after being empty (or after an admin
            // clears it via the "delete IP" action); a login attempt from a
            // different IP is rejected until then.
            $table->string('locked_ip')->nullable()->after('status');
            $table->timestamp('locked_ip_set_at')->nullable()->after('locked_ip');
            // Heartbeat updated on authenticated requests so the admin panel
            // can show who currently has their account open.
            $table->timestamp('last_seen_at')->nullable()->after('locked_ip_set_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['locked_ip', 'locked_ip_set_at', 'last_seen_at']);
        });
    }
};
