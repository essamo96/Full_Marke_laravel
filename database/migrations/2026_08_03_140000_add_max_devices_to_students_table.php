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
            // How many distinct devices this student may be locked to at
            // once (default 1). locked_device_id now stores a JSON array of
            // device ids instead of a single string once a student has more
            // than one slot — see Student::getLockedDeviceIdsAttribute().
            $table->unsignedTinyInteger('max_devices')->default(1)->after('force_logout_after');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('max_devices');
        });
    }
};
