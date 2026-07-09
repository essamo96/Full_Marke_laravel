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
        // 1. Drop existing foreign keys
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['study_branch_id']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['study_branch_id']);
        });

        // 2. Rename tables
        Schema::rename('branches', 'regions');
        Schema::rename('study_branches', 'branches');

        // 3. Rename columns
        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'region_id');
            $table->renameColumn('study_branch_id', 'branch_id');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'region_id');
            $table->renameColumn('study_branch_id', 'branch_id');
        });

        // 4. Update 'regions' structure
        Schema::table('regions', function (Blueprint $table) {
            $table->string('name_ar')->after('id')->nullable();
            $table->string('name_en')->after('name_ar')->nullable();
        });

        DB::statement("UPDATE regions SET name_ar = name, name_en = name");

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        // 5. Restore Foreign Keys
        Schema::table('students', function (Blueprint $table) {
            $table->foreign('region_id')->references('id')->on('regions')->restrictOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // To reverse, we do the exact opposite
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['branch_id']);
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropForeign(['branch_id']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        DB::statement("UPDATE regions SET name = name_ar");

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['name_ar', 'name_en']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->renameColumn('region_id', 'branch_id');
            $table->renameColumn('branch_id', 'study_branch_id');
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->renameColumn('region_id', 'branch_id');
            $table->renameColumn('branch_id', 'study_branch_id');
        });

        Schema::rename('regions', 'branches');
        Schema::rename('branches', 'study_branches');

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->restrictOnDelete();
            $table->foreign('study_branch_id')->references('id')->on('study_branches')->nullOnDelete();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('study_branch_id')->references('id')->on('study_branches')->nullOnDelete();
        });
    }
};
