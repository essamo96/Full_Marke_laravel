<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_notes', function (Blueprint $table) {
            $table->foreignId('student_id')->nullable()->after('group_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_alert')->default(false)->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('group_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('student_id');
            $table->dropColumn('is_alert');
        });
    }
};
