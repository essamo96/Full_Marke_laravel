<?php

namespace Database\Seeders;

use App\Models\Slider;
use Illuminate\Database\Seeder;

/**
 * The 3 homepage hero slides (image slider), matched by `sort` position so
 * re-running this is safe — it updates the existing row at that position
 * instead of creating duplicates. Assumes the 3 jpg files have already been
 * uploaded to storage/app/public/sliders/ on this server (they're excluded
 * from the GitHub Actions FTP deploy, so that has to happen manually).
 */
class HeroSlidesSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'sort' => 1,
                'status' => 1,
                'image' => 'sliders/hero_slide_1_writing.jpg',
                'video1' => null,
                'video2' => null,
                'title_ar' => 'أكاديمية العلامة الكاملة',
                'title_en' => 'Full Marks Academy',
                'desc_ar' => 'تعليم عصري يجمع بين المعلم الخبير والتقنية الحديثة، أونلاين وفي أي وقت يناسبك.',
                'desc_en' => 'Modern education blending expert teachers with the latest technology, online, anytime.',
                'btn1_text_ar' => 'سجل الآن',
                'btn1_text_en' => 'Apply Now',
                'btn1_link' => '#contact',
                'btn2_text_ar' => 'تصفح البرامج',
                'btn2_text_en' => 'Explore Programs',
                'btn2_link' => '#programs',
            ],
            [
                'sort' => 2,
                'status' => 1,
                'image' => 'sliders/hero_slide_2_chalk.jpg',
                'video1' => null,
                'video2' => null,
                'title_ar' => 'دروس تفاعلية أونلاين',
                'title_en' => 'Interactive Online Lessons',
                'desc_ar' => 'منصة تعليمية متكاملة تتابع تقدم الطالب خطوة بخطوة بأحدث الوسائل التفاعلية.',
                'desc_en' => "A complete learning platform that tracks every student's progress with the latest interactive tools.",
                'btn1_text_ar' => 'سجل الآن',
                'btn1_text_en' => 'Apply Now',
                'btn1_link' => '#contact',
                'btn2_text_ar' => 'تصفح البرامج',
                'btn2_text_en' => 'Explore Programs',
                'btn2_link' => '#programs',
            ],
            [
                'sort' => 3,
                'status' => 1,
                'image' => 'sliders/hero_slide_3_chalk2.jpg',
                'video1' => null,
                'video2' => null,
                'title_ar' => 'إبداع بلا حدود',
                'title_en' => 'Learning Without Limits',
                'desc_ar' => 'نمزج بين الإبداع والمنهج الأكاديمي لنصنع تجربة تعليمية ملهمة لكل طالب.',
                'desc_en' => "We blend creativity with academic rigor to make every student's learning journey inspiring.",
                'btn1_text_ar' => 'سجل الآن',
                'btn1_text_en' => 'Apply Now',
                'btn1_link' => '#contact',
                'btn2_text_ar' => 'تصفح البرامج',
                'btn2_text_en' => 'Explore Programs',
                'btn2_link' => '#programs',
            ],
        ];

        foreach ($slides as $slide) {
            Slider::updateOrCreate(['sort' => $slide['sort']], $slide);
        }
    }
}
