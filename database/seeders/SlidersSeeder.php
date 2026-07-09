<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slider;

class SlidersSeeder extends Seeder
{
    public function run(): void
    {
        $sliders = [
            [
                'title_ar' => 'أكاديمية العلامة الكاملة',
                'title_en' => 'Full Mark Academy',
                'desc_ar' => 'منصة رائدة للتدريب والتعليم عن بعد.',
                'desc_en' => 'Leading platform for training and distance learning.',
                'btn1_text_ar' => 'تواصل معنا',
                'btn1_text_en' => 'Contact Us',
                'btn1_link' => '#contact',
                'btn2_text_ar' => 'دوراتنا',
                'btn2_text_en' => 'Our Courses',
                'btn2_link' => '#courses',
                'image' => 'site/images/hero-animation-img/bg-main.jpg',
                'sort' => 1,
                'status' => 1
            ]
        ];

        foreach ($sliders as $slider) {
            Slider::firstOrCreate(
                ['title_en' => $slider['title_en']],
                $slider
            );
        }
    }
}
