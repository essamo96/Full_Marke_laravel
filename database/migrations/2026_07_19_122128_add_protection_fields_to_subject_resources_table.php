<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->string('processing_status')->default('ready')->after('url'); // ready|processing|failed
            $table->string('hls_path')->nullable()->after('processing_status');
            $table->string('encryption_key_path')->nullable()->after('hls_path');
            $table->unsignedInteger('duration_seconds')->nullable()->after('encryption_key_path');
            $table->string('original_filename')->nullable()->after('duration_seconds');
            $table->text('processing_error')->nullable()->after('original_filename');
        });
    }

    public function down(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropColumn([
                'processing_status',
                'hls_path',
                'encryption_key_path',
                'duration_seconds',
                'original_filename',
                'processing_error',
            ]);
        });
    }
};
