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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('site_email')->nullable();
            $table->string('site_phone')->nullable();
            $table->json('options')->nullable();
            
            // Drop old translatable columns
            $table->dropColumn([
                'seo_title',
                'seo_description',
                'seo_keywords',
                'maintenance_title',
                'maintenance_message'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            //
        });
    }
};
