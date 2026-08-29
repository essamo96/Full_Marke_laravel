<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('educational_unit_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_unit_id')->constrained('educational_units')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('educational_lesson_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educational_lesson_id')->constrained('educational_lessons')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('group_subject_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_resource_id')->constrained('subject_resources')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing unit group associations
        DB::table('educational_units')->whereNotNull('group_id')->orderBy('id')->chunk(100, function ($units) {
            $inserts = [];
            foreach ($units as $unit) {
                $inserts[] = [
                    'educational_unit_id' => $unit->id,
                    'group_id' => $unit->group_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('educational_unit_group')->insert($inserts);
        });

        // Migrate existing resource group associations
        DB::table('subject_resources')->whereNotNull('group_ids')->orderBy('id')->chunk(100, function ($resources) {
            $inserts = [];
            foreach ($resources as $resource) {
                $groupIds = json_decode($resource->group_ids, true);
                if (is_array($groupIds)) {
                    foreach ($groupIds as $groupId) {
                        // Ensure group exists before inserting, as json arrays don't have constraints
                        $groupExists = DB::table('groups')->where('id', $groupId)->exists();
                        if ($groupExists) {
                            $inserts[] = [
                                'subject_resource_id' => $resource->id,
                                'group_id' => $groupId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                }
            }
            if (!empty($inserts)) {
                DB::table('group_subject_resource')->insert($inserts);
            }
        });

        Schema::table('educational_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });

        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropColumn('group_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educational_units', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('educational_stage_id')->constrained('groups')->nullOnDelete();
        });

        Schema::table('subject_resources', function (Blueprint $table) {
            $table->json('group_ids')->nullable()->after('educational_lesson_id');
        });

        // Restore unit group associations
        DB::table('educational_unit_group')->orderBy('id')->chunk(100, function ($pivots) {
            foreach ($pivots as $pivot) {
                DB::table('educational_units')
                    ->where('id', $pivot->educational_unit_id)
                    ->update(['group_id' => $pivot->group_id]);
            }
        });

        // Restore resource group associations
        $resourceGroups = DB::table('group_subject_resource')->get()->groupBy('subject_resource_id');
        foreach ($resourceGroups as $resourceId => $pivots) {
            $groupIds = $pivots->pluck('group_id')->toArray();
            DB::table('subject_resources')
                ->where('id', $resourceId)
                ->update(['group_ids' => json_encode($groupIds)]);
        }

        Schema::dropIfExists('group_subject_resource');
        Schema::dropIfExists('educational_lesson_group');
        Schema::dropIfExists('educational_unit_group');
    }
};
