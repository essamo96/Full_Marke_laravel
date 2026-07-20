<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('teacher_id')->nullable()->after('group_id')->constrained()->nullOnDelete();
            $table->string('notes')->nullable()->after('status');
            $table->unique(['student_id', 'group_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'group_id', 'date']);
            $table->dropConstrainedForeignId('teacher_id');
            $table->dropColumn('notes');
        });
    }
};
