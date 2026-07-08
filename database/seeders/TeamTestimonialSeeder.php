<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TeamTestimonialSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data to avoid duplicates
        Team::query()->delete();
        Testimonial::query()->delete();

        // 1. Teams (5 members)
        $team1 = Team::create([
            'image' => 'site/images/img/students/teacher_1.png',
            'socials' => json_encode([['platform' => 'linkedin', 'link' => '#'], ['platform' => 'twitter', 'link' => '#']]),
            'display_order' => 1,
            'status' => 1,
        ]);
        $team1->translations()->createMany([
            ['locale' => 'ar', 'name' => 'د. أحمد السعيد', 'description' => 'مدرب آيلتس معتمد وخبير في تقييم اللغة الإنجليزية.', 'address1' => 'مدرس لغة إنجليزية'],
            ['locale' => 'en', 'name' => 'Dr. Ahmad Al-Saeed', 'description' => 'Certified IELTS trainer and English language assessment expert.', 'address1' => 'English Teacher'],
        ]);

        $team2 = Team::create([
            'image' => 'site/images/img/students/teacher_2.png',
            'socials' => json_encode([['platform' => 'linkedin', 'link' => '#']]),
            'display_order' => 2,
            'status' => 1,
        ]);
        $team2->translations()->createMany([
            ['locale' => 'ar', 'name' => 'أ. مريم ناصر', 'description' => 'خبيرة تدريس المحادثة وتطوير مهارات النطق.', 'address1' => 'مدربة محادثة'],
            ['locale' => 'en', 'name' => 'Ms. Mariam Naser', 'description' => 'Speaking instruction expert and articulation development specialist.', 'address1' => 'Speaking Trainer'],
        ]);

        $team3 = Team::create([
            'image' => 'site/images/img/students/teacher_3.png',
            'socials' => json_encode([['platform' => 'linkedin', 'link' => '#']]),
            'display_order' => 3,
            'status' => 1,
        ]);
        $team3->translations()->createMany([
            ['locale' => 'ar', 'name' => 'أ. سامر قباني', 'description' => 'متخصص في تقييم اختبارات OTE وتدريب المستوى المتقدم.', 'address1' => 'أخصائي تقييم'],
            ['locale' => 'en', 'name' => 'Mr. Samer Kabbani', 'description' => 'OTE assessment specialist and advanced level trainer.', 'address1' => 'Assessment Specialist'],
        ]);

        $team4 = Team::create([
            'image' => 'site/images/img/students/teacher_4.png', 
            'socials' => json_encode([['platform' => 'linkedin', 'link' => '#']]),
            'display_order' => 4,
            'status' => 1,
        ]);
        $team4->translations()->createMany([
            ['locale' => 'ar', 'name' => 'د. خالد عبد الرحمن', 'description' => 'أستاذ دكتور في اللغويات التطبيقية وتدريس اللغات.', 'address1' => 'أستاذ لغويات'],
            ['locale' => 'en', 'name' => 'Dr. Khalid Abdulrahman', 'description' => 'Professor in Applied Linguistics and Language Teaching.', 'address1' => 'Linguistics Professor'],
        ]);

        $team5 = Team::create([
            'image' => 'site/images/img/students/teacher_1.png', 
            'socials' => json_encode([['platform' => 'linkedin', 'link' => '#']]),
            'display_order' => 5,
            'status' => 1,
        ]);
        $team5->translations()->createMany([
            ['locale' => 'ar', 'name' => 'أ. سارة المفتي', 'description' => 'مدربة معتمدة لبرامج الأطفال واليافعين بطرق تفاعلية.', 'address1' => 'مدربة برامج أطفال'],
            ['locale' => 'en', 'name' => 'Ms. Sarah Al-Mufti', 'description' => 'Certified trainer for kids and teens programs with interactive methods.', 'address1' => 'Kids Programs Trainer'],
        ]);

        // 2. Testimonials (6 items)
        $test1 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png',
            'display_order' => 1,
            'status' => 1,
        ]);
        $test1->translations()->createMany([
            ['locale' => 'ar', 'name' => 'أحمد السعيد', 'position' => 'طالب آيلتس', 'message' => 'اجتزت اختبار آيلتس الأكاديمي بنتيجة 7.5 بفضل الدورة المكثفة في الأكاديمية.'],
            ['locale' => 'en', 'name' => 'Ahmad Al-Saeed', 'position' => 'IELTS Student', 'message' => 'I passed my academic IELTS test with a score of 7.5 thanks to the intensive courses at Full Mark.'],
        ]);

        $test2 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png',
            'display_order' => 2,
            'status' => 1,
        ]);
        $test2->translations()->createMany([
            ['locale' => 'ar', 'name' => 'مريم ناصر', 'position' => 'دورة محادثة', 'message' => 'مركز راقي ومتطور! كانت الأوقات المرنة حاسمة بالنسبة لي كصيدلانية عاملة.'],
            ['locale' => 'en', 'name' => 'Mariam Naser', 'position' => 'Speaking Course', 'message' => 'An elegant and advanced center! The flexible timings were crucial for me as a working pharmacist.'],
        ]);

        $test3 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png',
            'display_order' => 3,
            'status' => 1,
        ]);
        $test3->translations()->createMany([
            ['locale' => 'ar', 'name' => 'سامر قباني', 'position' => 'متقدم لـ OTE', 'message' => 'كان خوض اختبار العلامة الكاملة (OTE) في المركز تجربة سلسة للغاية.'],
            ['locale' => 'en', 'name' => 'Samer Kabbani', 'position' => 'OTE Applicant', 'message' => 'Full Mark Test of English (OTE) at their center was a seamless experience.'],
        ]);

        $test4 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png', 
            'display_order' => 4,
            'status' => 1,
        ]);
        $test4->translations()->createMany([
            ['locale' => 'ar', 'name' => 'يوسف عبد الله', 'position' => 'طالب جامعي', 'message' => 'المركز يوفر بيئة تعليمية محفزة وممتازة. استفدت كثيراً من دورات التحضير.'],
            ['locale' => 'en', 'name' => 'Yousef Abdullah', 'position' => 'University Student', 'message' => 'The center provides an excellent and motivating learning environment.'],
        ]);

        $test5 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png', 
            'display_order' => 5,
            'status' => 1,
        ]);
        $test5->translations()->createMany([
            ['locale' => 'ar', 'name' => 'ليلى محمود', 'position' => 'موظفة بنك', 'message' => 'دورة اللغة الإنجليزية للأعمال كانت ممتازة جداً وساعدتني في تطوير مهارات التواصل.'],
            ['locale' => 'en', 'name' => 'Laila Mahmoud', 'position' => 'Bank Employee', 'message' => 'The Business English course was excellent and helped me significantly develop my communication skills.'],
        ]);

        $test6 = Testimonial::create([
            'image' => 'site/images/img/students/student3_new.png', 
            'display_order' => 6,
            'status' => 1,
        ]);
        $test6->translations()->createMany([
            ['locale' => 'ar', 'name' => 'عصام حليوة', 'position' => 'طالب ثانوي', 'message' => 'مركز راقي ومتطور جداً، أنصح الجميع به! المدربون متعاونون جداً.'],
            ['locale' => 'en', 'name' => 'Essam Hleiwa', 'position' => 'High School Student', 'message' => 'A very advanced and classy center, I highly recommend it! Trainers are very helpful.'],
        ]);
    }
}
