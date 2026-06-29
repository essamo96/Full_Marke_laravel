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
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Hero section media (videos + fallback still image)
            $table->string('hero_video_1')->nullable();
            $table->string('hero_video_2')->nullable();
            $table->string('hero_video_1_mobile')->nullable();
            $table->string('hero_video_2_mobile')->nullable();
            $table->string('about_video')->nullable();
            $table->string('about_video_mobile')->nullable();
            $table->string('hero_still_image')->nullable();

            // Social media platforms: [{platform, url, icon}, ...]
            $table->json('social_links')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->text('seo_keywords')->nullable();

            // Maintenance / closure screen
            $table->boolean('maintenance_mode')->default(0);
            $table->string('maintenance_title')->nullable();
            $table->text('maintenance_message')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
