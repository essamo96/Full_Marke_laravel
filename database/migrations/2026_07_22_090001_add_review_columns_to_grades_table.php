<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->timestamp('teacher_reviewed_at')->nullable()->after('auto_submitted');
            $table->timestamp('admin_approved_at')->nullable()->after('teacher_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['teacher_reviewed_at', 'admin_approved_at']);
        });
    }
};
