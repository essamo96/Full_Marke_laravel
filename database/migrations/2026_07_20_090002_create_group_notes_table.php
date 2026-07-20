<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_notes');
    }
};
