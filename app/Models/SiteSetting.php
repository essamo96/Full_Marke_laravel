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
        'maintenance_mode',
        'show_translation_button',
        'completed_courses_count',
        'registered_students_count',
        'training_hours_count',
        'site_email',
        'site_phone',
        'options',
    ];

    public function translations()
    {
        return $this->hasMany(SiteSettingTranslation::class, 'site_setting_id');
    }

    public function translation()
    {
        return $this->hasOne(SiteSettingTranslation::class, 'site_setting_id')->where('locale', app()->getLocale());
    }

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'maintenance_mode' => 'boolean',
            'show_translation_button' => 'boolean',
            'options' => 'array',
        ];
    }

    /**
     * The site has exactly one settings record (id = 1). Falls back to an
     * unsaved instance with sensible defaults when none exists yet.
     */
    public static function current(): self
    {
        return static::find(1) ?? new static([
            'hero_video_1' => 'site/images/slider1.mp4',
            'hero_video_2' => 'site/images/slider2.mp4',
            'about_video' => 'site/images/aboutUs.mp4',
            'hero_still_image' => 'site/images/hero-animation-img/bg-main.jpg',
            'social_links' => [],
            'show_translation_button' => true,
            'completed_courses_count' => 320,
            'registered_students_count' => 8700,
            'training_hours_count' => 1500,
            'site_email' => 'info@fullmark.com',
            'site_phone' => '+123456789',
            'options' => [
                'currency' => 'JOD',
            ],
        ]);
    }
}
