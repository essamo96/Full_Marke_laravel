<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('national_id')->nullable()->unique()->after('full_name_en');
            $table->boolean('is_child')->default(false)->after('national_id');
            $table->foreignId('guardian_id')->nullable()->after('is_child')->constrained('guardians')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guardian_id');
            $table->dropColumn(['national_id', 'is_child']);
        });
    }
};
