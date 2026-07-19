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
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->constrained('exams')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->integer('time_taken_minutes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['exam_id', 'started_at', 'time_taken_minutes']);
        });
    }
};
