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
            // Replaces IP-based locking: a random token stored in a long-lived
            // browser cookie, unique per browser/device and unaffected by
            // network/IP changes. locked_ip is kept as a read-only "last seen
            // from" info column for the admin panel, no longer used to allow
            // or block logins.
            $table->string('locked_device_id')->nullable()->after('locked_ip_set_at');
            $table->timestamp('locked_device_id_set_at')->nullable()->after('locked_device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['locked_device_id', 'locked_device_id_set_at']);
        });
    }
};
