<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        Faq::query()->delete();

        // FAQs
        $faq1 = Faq::create([
            'status' => 1,
        ]);
        $faq1->translations()->createMany([
            ['locale' => 'ar', 'question' => 'كيف يمكنني التسجيل في دورات الأكاديمية؟', 'answer' => 'يمكنك التسجيل من خلال موقعنا الإلكتروني، أو من خلال زيارة فرع الأكاديمية مباشرة. فريق الدعم متاح لمساعدتك في أي وقت.'],
            ['locale' => 'en', 'question' => 'How can I register for the academy courses?', 'answer' => 'You can register through our website, or by visiting the academy branch directly. Our support team is available to assist you at any time.'],
        ]);

        $faq2 = Faq::create([
            'status' => 1,
        ]);
        $faq2->translations()->createMany([
            ['locale' => 'ar', 'question' => 'هل يحصل المتدرب على شهادة معتمدة بعد إتمام الدورة؟', 'answer' => 'نعم، جميع الدورات تقدم شهادات معتمدة من الأكاديمية تفيد باجتياز المتدرب للدورة ومستوى الكفاءة الذي حققه.'],
            ['locale' => 'en', 'question' => 'Does the trainee receive a certified certificate after completing the course?', 'answer' => 'Yes, all courses provide certified certificates from the academy stating that the trainee has passed the course and the level of proficiency achieved.'],
        ]);

        $faq3 = Faq::create([
            'status' => 1,
        ]);
        $faq3->translations()->createMany([
            ['locale' => 'ar', 'question' => 'هل تتوفر دورات تحضيرية لاختبارات الآيلتس (IELTS)؟', 'answer' => 'بالتأكيد، نوفر برامج تدريبية مكثفة وشاملة للتحضير لاختبارات الآيلتس مع تقديم اختبارات تجريبية مستمرة لتقييم المستوى.'],
            ['locale' => 'en', 'question' => 'Are there preparatory courses for IELTS exams?', 'answer' => 'Absolutely, we provide intensive and comprehensive training programs to prepare for IELTS exams, along with continuous mock tests to assess the level.'],
        ]);

        $faq4 = Faq::create([
            'status' => 1,
        ]);
        $faq4->translations()->createMany([
            ['locale' => 'ar', 'question' => 'ما هي أوقات الدوام الرسمية للأكاديمية؟', 'answer' => 'نعمل من السبت إلى الخميس، من الساعة 8 صباحاً وحتى 8 مساءً. لمزيد من التفاصيل يمكنك التواصل معنا عبر الواتساب.'],
            ['locale' => 'en', 'question' => 'What are the official working hours of the academy?', 'answer' => 'We work from Saturday to Thursday, from 8 AM to 8 PM. For more details, you can contact us via WhatsApp.'],
        ]);
        
        $faq5 = Faq::create([
            'status' => 1,
        ]);
        $faq5->translations()->createMany([
            ['locale' => 'ar', 'question' => 'هل هناك تحديد مستوى قبل البدء بأي دورة؟', 'answer' => 'نعم، يتوجب على جميع المشتركين الجدد الخضوع لاختبار تحديد مستوى لضمان وضعهم في المجموعة التي تناسب كفاءتهم.'],
            ['locale' => 'en', 'question' => 'Is there a placement test before starting any course?', 'answer' => 'Yes, all new subscribers must take a placement test to ensure they are placed in the group that suits their proficiency.'],
        ]);
    }
}
