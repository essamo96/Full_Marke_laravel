<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Units and lessons used to be hard-deleted. Deleting a lesson silently turned
 * its videos into "general" resources (the FK was ON DELETE SET NULL) and could
 * not be undone. They now soft-delete like resources do, so a delete can be
 * reverted from the undo toast or the trash.
 */
return new class extends Migration
{
    private array $tables = ['educational_units', 'educational_lessons'];

    public function up(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->softDeletes();
                $table->string('deleted_by_type', 20)->nullable();
                $table->unsignedBigInteger('deleted_by_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropColumn(['deleted_at', 'deleted_by_type', 'deleted_by_id']);
            });
        }
    }
};
