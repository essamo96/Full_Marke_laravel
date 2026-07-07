<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;
use App\Models\PagesTranslations;
use Illuminate\Support\Facades\File;

class PagesSeeder extends Seeder
{
    public function run()
    {
        // Ensure the video is copied to storage so it works in the backend and frontend
        $videoPath = null;
        $sourceVideo = public_path('site/images/aboutUs.mp4');
        $destDir = storage_path('app/public/uploads/pages/videos');
        
        if (File::exists($sourceVideo)) {
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0777, true, true);
            }
            File::copy($sourceVideo, $destDir . '/aboutUs.mp4');
            $videoPath = 'uploads/pages/videos/aboutUs.mp4';
        }

        // 1. About Us
        $aboutPage = Page::updateOrCreate(
            ['slug' => 'about_us'],
            [

                'status' => 1,
                'video' => $videoPath,
            ]
        );
        PagesTranslations::updateOrCreate(['page_id' => $aboutPage->id, 'locale' => 'ar'], [
            'title' => 'المؤسسة الأكاديمية الرائدة للتدريب',
            'details' => '<p>تقف أكاديمية العلامة الكاملة في طليعة تعليم اللغات والتقييم الدولي. بصفتنا مركزًا معتمدًا لاختبار Full Mark Test of English (OTE)، نقدم شهادات معترف بها عالميًا إلى جانب تدريب أكاديمي متميز مُعد خصيصًا لاجتياز اختبار آيلتس، المستويات العامة، والمحادثة المهنية للمؤسسات.</p>'
        ]);
        PagesTranslations::updateOrCreate(['page_id' => $aboutPage->id, 'locale' => 'en'], [
            'title' => 'Leading Academic Training Institution',
            'details' => '<p>FULL MARKS ACADEMY stands at the forefront of language education and testing. As an approved Full Mark Test of English (OTE) center, we deliver globally recognized certifications alongside premier academic instruction tailored for IELTS preparation, general levels, and corporate business communication.</p>'
        ]);

        // 2. Features
        $featuresPage = Page::updateOrCreate(
            ['slug' => 'features'],
            [

                'status' => 1,
            ]
        );
        PagesTranslations::updateOrCreate(['page_id' => $featuresPage->id, 'locale' => 'ar'], [
            'title' => 'الركائز الأساسية للنجاح الأكاديمي',
            'details' => '<p>جدول مرن، طاقم تدريس، القيمة الأكاديمية، تطوير الطالب.</p>'
        ]);
        PagesTranslations::updateOrCreate(['page_id' => $featuresPage->id, 'locale' => 'en'], [
            'title' => 'Core Pillars of Academic Success',
            'details' => '<p>Timetable, Instructors, Academic Value, Student Growth.</p>'
        ]);

        // 3. Services
        $servicesPage = Page::updateOrCreate(
            ['slug' => 'services'],
            [

                'status' => 1,
            ]
        );
        PagesTranslations::updateOrCreate(['page_id' => $servicesPage->id, 'locale' => 'ar'], [
            'title' => 'بوابات التسجيل والحجز الفوري',
            'details' => '<p>منهج الأكاديمية، حجز تحديد المستوى، تسجيل في دورة.</p>'
        ]);
        PagesTranslations::updateOrCreate(['page_id' => $servicesPage->id, 'locale' => 'en'], [
            'title' => 'Enroll and Booking Gateways',
            'details' => '<p>Our Syllabus, Placement Booking, Course Booking.</p>'
        ]);

        // 4. Training Hours
        $trainingPage = Page::updateOrCreate(
            ['slug' => 'training_hours'],
            [

                'status' => 1,
            ]
        );
        PagesTranslations::updateOrCreate(['page_id' => $trainingPage->id, 'locale' => 'ar'], [
            'title' => 'ساعات تدريبية',
            'details' => '<p>15000 ساعة تدريبية، 320 دورة، 8700 طالب.</p>'
        ]);
        PagesTranslations::updateOrCreate(['page_id' => $trainingPage->id, 'locale' => 'en'], [
            'title' => 'Training Hours',
            'details' => '<p>15000 Training Hours, 320 Total Courses, 8700 Enrolled Students.</p>'
        ]);
    }
}
