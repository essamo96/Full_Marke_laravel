<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run()
    {
        if (News::count() == 0) {
            $news1 = News::create([
                'image' => 'site/images/img/banner/ote_hall.png',
                'status' => 1,
                'created_at' => now()->subDays(10),
            ]);
            $news1->translations()->createMany([
                ['locale' => 'ar', 'title' => 'جلسات اختبار OTE جديدة', 'description' => 'المواعيد المعتمدة لجلسات تقييم اختبار العلامة الكاملة للغة الإنجليزية القادمة مفتوحة للتسجيل الآن.'],
                ['locale' => 'en', 'title' => 'New OTE Placement Sessions', 'description' => 'Approved dates for the upcoming Full Mark Test of English assessment are now open for registration.'],
            ]);

            $news2 = News::create([
                'image' => 'site/images/img/news/news2.png',
                'status' => 1,
                'created_at' => now()->subDays(5),
            ]);
            $news2->translations()->createMany([
                ['locale' => 'ar', 'title' => 'بدء التسجيل لدورة الآيلتس المكثفة', 'description' => 'سجل الآن في المجموعة الأكاديمية الجديدة للتحضير لاختبار آيلتس بإشراف مدربين مؤهلين واختبارات تجريبية.'],
                ['locale' => 'en', 'title' => 'IELTS Prep Program Starting', 'description' => 'Register for our academic IELTS training cohort led by certified British trainers with mock testing.'],
            ]);

            $news3 = News::create([
                'image' => 'site/images/img/news/news3.png',
                'status' => 1,
                'created_at' => now(),
            ]);
            $news3->translations()->createMany([
                ['locale' => 'ar', 'title' => 'ورشة عمل المحادثة الأكاديمية', 'description' => 'قم بتحسين صياغة المقالات الأكاديمية وطلاقة المحادثة العلمية من خلال ورشنا التدريبية المركزة.'],
                ['locale' => 'en', 'title' => 'Academic Speaking & Writing', 'description' => 'Improve your essay structures and academic speaking fluency with our intensive workshops.'],
            ]);
        }
    }
}
