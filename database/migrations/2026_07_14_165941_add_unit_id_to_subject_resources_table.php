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
        Schema::table('subject_resources', function (Blueprint $table) {
            $table->foreignId('educational_unit_id')->nullable()->constrained('educational_units')->nullOnDelete();
            $table->string('category')->nullable()->default('link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_resources', function (Blueprint $table) {
            //
        });
    }
};
