<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->unsignedInteger('tab_switch_count')->default(0)->after('time_taken_minutes');
            $table->unsignedInteger('fullscreen_exit_count')->default(0)->after('tab_switch_count');
            $table->boolean('auto_submitted')->default(false)->after('fullscreen_exit_count');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['tab_switch_count', 'fullscreen_exit_count', 'auto_submitted']);
        });
    }
};
