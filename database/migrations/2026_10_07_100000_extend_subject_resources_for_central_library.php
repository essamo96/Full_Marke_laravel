<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `subject_resources` is the central repository of the resource library
 * ("upload once, assign anywhere"): one row per physical file/link, no matter
 * how many groups, units or lessons it is shown in.
 *
 * This migration only adds what the repository was missing:
 *   - file metadata (size / mime / fingerprint) so duplicates can be detected
 *   - who uploaded it, and who switched it off (kill switch audit)
 *   - who deleted it, for both admins and teachers (deleted_by was admin-only)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->unsignedBigInteger('size_bytes')->nullable()->after('original_filename');
            $table->string('mime_type', 120)->nullable()->after('size_bytes');
            $table->string('content_hash', 64)->nullable()->after('mime_type');

            $table->string('created_by_type', 20)->nullable();
            $table->unsignedBigInteger('created_by_id')->nullable();

            $table->timestamp('deactivated_at')->nullable();
            $table->string('deactivated_by_type', 20)->nullable();
            $table->unsignedBigInteger('deactivated_by_id')->nullable();

            $table->string('deleted_by_type', 20)->nullable()->after('deleted_by');

            $table->index(['subject_id', 'is_active'], 'subject_resources_subject_active_idx');
            $table->index('content_hash', 'subject_resources_content_hash_idx');
            $table->index(['created_by_type', 'created_by_id'], 'subject_resources_creator_idx');
        });

        // Until now only admins could delete, and deleted_by pointed at admins.id.
        DB::table('subject_resources')
            ->whereNotNull('deleted_by')
            ->whereNull('deleted_by_type')
            ->update(['deleted_by_type' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropIndex('subject_resources_subject_active_idx');
            $table->dropIndex('subject_resources_content_hash_idx');
            $table->dropIndex('subject_resources_creator_idx');

            $table->dropColumn([
                'size_bytes', 'mime_type', 'content_hash',
                'created_by_type', 'created_by_id',
                'deactivated_at', 'deactivated_by_type', 'deactivated_by_id',
                'deleted_by_type',
            ]);
        });
    }
};
