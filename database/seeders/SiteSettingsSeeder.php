<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $setting = SiteSetting::updateOrCreate(
            ['id' => 1],
            [
                'hero_video_1' => 'site/images/slider1.mp4',
                'hero_video_2' => 'site/images/slider2.mp4',
                'about_video' => 'site/images/aboutUs.mp4',
                'hero_still_image' => 'site/images/hero-animation-img/bg-main.jpg',
                'social_links' => [],
                'maintenance_mode' => 0,
                'show_translation_button' => 1,
                'completed_courses_count' => 320,
                'registered_students_count' => 8700,
                'training_hours_count' => 1500,
                'site_email' => 'info@fullmark-academy.com',
                'site_phone' => '+970591234567',
                'options' => json_encode([
                    'show_contact_form' => true,
                    'enable_newsletter' => false,
                    'enable_live_chat' => false,
                    'show_registration_button' => true,
                ], JSON_UNESCAPED_UNICODE),
            ]
        );

        // SEO and Arabic translations
        $setting->translations()->updateOrCreate(
            ['locale' => 'ar'],
            [
                'seo_title' => 'أكاديمية العلامة الكاملة | طريقك للتفوق والنجاح',
                'seo_description' => 'منصة أكاديمية العلامة الكاملة تقدم أفضل الدورات التعليمية لتأهيل الطلاب بأساليب حديثة. اشترك الآن لتحقيق التفوق الأكاديمي والمهني ولتحصل على نتائج مضمونة في مسيرتك العلمية.',
                'seo_keywords' => 'أكاديمية العلامة الكاملة, دورات تعليمية, تعليم عن بعد, دورات أونلاين, تفوق دراسي, توجيهي, تعلم لغات, آيلتس',
                'maintenance_title' => 'الموقع تحت الصيانة',
                'maintenance_message' => 'نحن نقوم ببعض التحديثات المهمة للارتقاء بتجربة المستخدم. سنعود إليكم قريباً، شكراً لتفهمكم.',
                'site_address' => 'فلسطين - مدينة غزة - شارع الجلاء',
            ]
        );

        // SEO and English translations
        $setting->translations()->updateOrCreate(
            ['locale' => 'en'],
            [
                'seo_title' => 'Full Mark Academy | Your Path to Excellence',
                'seo_description' => 'Full Mark Academy offers top-tier educational courses to empower students with modern methods. Join now to achieve academic and professional success with guaranteed results.',
                'seo_keywords' => 'Full Mark Academy, online courses, distance learning, academic excellence, e-learning, IELTS, Tawjihi',
                'maintenance_title' => 'Site Under Maintenance',
                'maintenance_message' => 'We are performing scheduled maintenance to improve your experience. We will be back shortly. Thank you for your patience.',
                'site_address' => 'Palestine - Gaza City - Al-Jalaa St',
            ]
        );
    }
}
