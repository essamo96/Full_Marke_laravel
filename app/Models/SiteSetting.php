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
        'show_translation_button',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'maintenance_mode' => 'boolean',
            'show_translation_button' => 'boolean',
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
            'show_translation_button' => true,
            'seo_title' => 'أكاديمية العلامة الكاملة | Full Mark Academy',
            'seo_description' => 'أكاديمية العلامة الكاملة تقدم أفضل البرامج التعليمية التفاعلية والدورات الشاملة التي تضمن تفوقك الدراسي وتأهيلك لمستقبل مشرق.',
            'seo_keywords' => 'أكاديمية العلامة الكاملة, تعليم, دورات, توجيهي, تقوية, Full Mark Academy, Education',
        ]);
    }
}
