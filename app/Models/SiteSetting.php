<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'hero_video_1',
        'hero_video_2',
        'hero_video_1_mobile',
        'hero_video_2_mobile',
        'about_video',
        'about_video_mobile',
        'hero_still_image',
        'social_links',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'maintenance_mode',
        'maintenance_title',
        'maintenance_message',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'maintenance_mode' => 'boolean',
        ];
    }

    /**
     * The site has exactly one settings record (id = 1). Falls back to an
     * unsaved instance with sensible defaults when none exists yet.
     */
    public static function current(): self
    {
        return static::first() ?? new static([
            'hero_video_1' => 'site/images/slider1.mp4',
            'hero_video_2' => 'site/images/slider2.mp4',
            'about_video' => 'site/images/aboutUs.mp4',
            'hero_still_image' => 'site/images/hero-animation-img/bg-main.jpg',
            'social_links' => [],
        ]);
    }
}
