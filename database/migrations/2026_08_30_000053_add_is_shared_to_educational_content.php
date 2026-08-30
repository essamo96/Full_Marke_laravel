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
        Schema::table('educational_units', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('name_en');
        });
        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('name_en');
        });
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->boolean('is_shared')->default(false)->after('allow_download');
        });

        // Data preservation logic
        $units = \App\Models\EducationalUnit::doesntHave('groups')->get();
        foreach ($units as $unit) {
            $unit->is_shared = true;
            $unit->save();
        }

        $lessons = \App\Models\EducationalLesson::doesntHave('groups')->get();
        foreach ($lessons as $lesson) {
            $lesson->is_shared = true;
            $lesson->save();
        }

        $resources = \App\Models\SubjectResource::doesntHave('groups')->get();
        foreach ($resources as $resource) {
            $resource->is_shared = true;
            $resource->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('educational_units', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
        Schema::table('educational_lessons', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->dropColumn('is_shared');
        });
    }
};
