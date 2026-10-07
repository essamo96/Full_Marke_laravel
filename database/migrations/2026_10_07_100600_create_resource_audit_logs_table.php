<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Journal of every change made through the resource library.
 *
 * It does two jobs:
 *  - audit trail: who hid / shared / excluded / deleted what, and when
 *  - undo: `before` holds a snapshot of every row the action touched, so the
 *    undo toast (and the "recent actions" list) can put things back exactly.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('actor_type', 20);
            $table->unsignedBigInteger('actor_id');

            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('action', 60);
            $table->string('target_type', 60)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('summary', 255)->nullable();

            $table->json('before')->nullable();
            $table->json('meta')->nullable();

            $table->timestamp('undone_at')->nullable();
            $table->string('undone_by_type', 20)->nullable();
            $table->unsignedBigInteger('undone_by_id')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->index(['subject_id', 'created_at'], 'resource_audit_logs_subject_idx');
            $table->index(['target_type', 'target_id'], 'resource_audit_logs_target_idx');
            $table->index(['actor_type', 'actor_id'], 'resource_audit_logs_actor_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_audit_logs');
    }
};
