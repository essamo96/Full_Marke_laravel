<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_content_grants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->morphs('grantable');
            $table->string('source', 32)->default('transfer');
            $table->foreignId('from_group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'grantable_type', 'grantable_id'],
                'student_content_grants_unique'
            );
            $table->index(['student_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_content_grants');
    }
};
