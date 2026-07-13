<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'slug' => 'tawjihi',
                'name_en' => 'Tawjihi Program',
                'name_ar' => 'برنامج التوجيهي',
                'type' => 'high',
                'short_description' => 'Comprehensive preparation for high school students with elite teachers to ensure top scores and academic excellence.',
                'long_description' => 'تأهيل شامل لطلبة الثانوية العامة مع نخبة من أفضل المعلمين لضمان التفوق والحصول على العلامة الكاملة.',
                'image' => 'site/images/img/programs/prog1.png',
                'sort_order' => 1,
                'subjects' => [
                    [
                        'name_en' => 'English Language',
                        'name_ar' => 'اللغة الإنجليزية',
                        'description_en' => 'Master English grammar and school curriculum with simple, effective learning methods to secure top scores.',
                        'description_ar' => 'شرح كامل لجميع قواعد ومنهاج اللغة الإنجليزية للتوجيهي بأسلوب مبسط وممتع وضمان العلامة الكاملة.',
                        'image' => 'site/images/img/programs/prog1.png',
                        'reg_start_date' => '2026-07-01',
                        'reg_end_date' => '2026-07-15',
                        'fee' => 120,
                        'total_fee' => 130,
                        'discount_percent' => 15,
                        'google_tags' => ['GoogleClassroom', 'GoogleMeet', 'GoogleForms'],
                    ],
                    [
                        'name_en' => 'Mathematics',
                        'name_ar' => 'الرياضيات',
                        'description_en' => 'Deep dive into calculus and algebra with comprehensive solving of past exam papers and predicted models.',
                        'description_ar' => 'فهم عميق للتفاضل والتكامل والنهايات مع حل أسئلة السنوات السابقة ونماذج امتحانات متوقعة.',
                        'image' => 'site/images/img/programs/prog1.png',
                        'reg_start_date' => '2026-07-01',
                        'reg_end_date' => '2026-07-20',
                        'fee' => 140,
                        'total_fee' => 150,
                        'discount_percent' => 10,
                        'google_tags' => ['Classroom', 'Workspace', 'Jamboard'],
                    ],
                ],
            ],
            [
                'slug' => 'children',
                'name_en' => 'Children Program',
                'name_ar' => 'برنامج الأطفال',
                'type' => 'primary',
                'short_description' => 'Fun, interactive language learning designed to build a strong foundation for young learners using modern tools.',
                'long_description' => 'تعليم تفاعلي مرح يهدف إلى بناء لغوي قوي للأطفال من سن مبكر باستخدام وسائل تعليمية مبتكرة.',
                'image' => 'site/images/img/programs/prog2.png',
                'sort_order' => 2,
                'subjects' => [
                    [
                        'name_en' => 'Phonics & Reading',
                        'name_ar' => 'الصوتيات والقراءة',
                        'description_en' => 'Interactive English phonics training using Jolly Phonics method to build spelling and reading confidence.',
                        'description_ar' => 'تعليم نطق الحروف والكلمات الإنجليزية بطريقة الصوتيات التفاعلية (Jolly Phonics) وبناء مخزون لغوي قوي.',
                        'image' => 'site/images/img/programs/prog2.png',
                        'reg_start_date' => '2026-06-25',
                        'reg_end_date' => '2026-07-10',
                        'fee' => 80,
                        'min_payment' => 90,
                        'discount_percent' => 20,
                        'google_tags' => ['GoogleClassroom', 'GoogleMeet', 'YouTubeKids'],
                    ],
                    [
                        'name_en' => 'Creative English',
                        'name_ar' => 'اللغة الإنجليزية الإبداعية',
                        'description_en' => 'Combining English learning with drawing, storytelling, and movement games for enhanced retention.',
                        'description_ar' => 'دمج تعليم اللغة الإنجليزية بالرسم والألعاب والأنشطة الحركية التفاعلية لترسيخ المفاهيم.',
                        'image' => 'site/images/img/programs/prog2.png',
                        'reg_start_date' => '2026-06-25',
                        'reg_end_date' => '2026-07-12',
                        'fee' => 95,
                        'min_payment' => 100,
                        'discount_percent' => null,
                        'google_tags' => ['Classroom', 'Workspace', 'Slides'],
                    ],
                ],
            ],
            [
                'slug' => 'speech',
                'name_en' => 'Speech Therapy Program',
                'name_ar' => 'برنامج النطق',
                'type' => 'general',
                'short_description' => 'Specialized therapy sessions to resolve speech difficulties and enhance articulation for children and adults.',
                'long_description' => 'جلسات متخصصة لعلاج مشاكل النطق والتخاطب وتحسين النطق السليم لدى الأطفال والبالغين بأحدث الأساليب.',
                'image' => 'site/images/img/programs/prog3.png',
                'sort_order' => 3,
                'subjects' => [
                    [
                        'name_en' => 'Articulation & Speech Correction',
                        'name_ar' => 'مخارج الحروف وتعديل النطق',
                        'description_en' => 'Specialized sessions addressing stuttering, lisping, and articulation errors overseen by licensed therapists.',
                        'description_ar' => 'جلسات فردية مكثفة لتصحيح التلعثم وعلاج اللدغات ومخارج الحروف غير السليمة بإشراف أخصائي نطق مرخص.',
                        'image' => 'site/images/img/programs/prog3.png',
                        'reg_start_date' => '2026-07-05',
                        'reg_end_date' => '2026-07-25',
                        'fee' => 150,
                        'min_payment' => 160,
                        'discount_percent' => 25,
                        'google_tags' => ['GoogleMeet', 'GoogleForms', 'GoogleKeep'],
                    ],
                    [
                        'name_en' => 'Language Delay Treatment',
                        'name_ar' => 'علاج التأخر اللغوي',
                        'description_en' => 'Vocabulary expansion and sentence formulation strategies for kids suffering from speech delay.',
                        'description_ar' => 'تحفيز التواصل اللغوي واللفظي لدى الأطفال المتأخرين في الكلام وزيادة فهمهم للمفردات وتكوين الجمل.',
                        'image' => 'site/images/img/programs/prog3.png',
                        'reg_start_date' => '2026-07-05',
                        'reg_end_date' => '2026-07-30',
                        'fee' => 160,
                        'min_payment' => 170,
                        'discount_percent' => 15,
                        'google_tags' => ['GoogleClassroom', 'GoogleMeet', 'GoogleDocs'],
                    ],
                ],
            ],
            [
                'slug' => 'rehab',
                'name_en' => 'Rehabilitation Program',
                'name_ar' => 'برنامج التأهيلي',
                'type' => 'general',
                'short_description' => 'Intensive training designed to build key academic skills and prepare students for integration into standard tracks.',
                'long_description' => 'برنامج مكثف لتطوير المهارات الأكاديمية والاجتماعية وتأهيل الطلاب للاندماج الفعال في البيئات التعليمية.',
                'image' => 'site/images/img/programs/prog4.png',
                'sort_order' => 4,
                'subjects' => [
                    [
                        'name_en' => 'Behavioral & Social Rehabilitation',
                        'name_ar' => 'التأهيل السلوكي والاجتماعي',
                        'description_en' => 'Preparing youth behaviorally for academic settings, boosting confidence, and teaching daily social interaction skills.',
                        'description_ar' => 'تهيئة الأطفال واليافعين سلوكياً للاندماج الأكاديمي، تعزيز الثقة بالنفس، وإدارة المهارات الاجتماعية اليومية.',
                        'image' => 'site/images/img/programs/prog4.png',
                        'reg_start_date' => '2026-07-10',
                        'reg_end_date' => '2026-07-28',
                        'fee' => 180,
                        'min_payment' => 190,
                        'discount_percent' => 30,
                        'google_tags' => ['GoogleMeet', 'GoogleClassroom', 'Jamboard'],
                    ],
                    [
                        'name_en' => 'Learning Difficulties Support',
                        'name_ar' => 'دعم صعوبات التعلم',
                        'description_en' => 'Special strategies to simplify reading and writing for students with ADHD or dyslexia to keep pace with studies.',
                        'description_ar' => 'استراتيجيات متطورة لتبسيط الفهم لطلاب صعوبات التعلم (الديسليكسيا، تشتت الانتباه) ومساعدتهم دراسياً.',
                        'image' => 'site/images/img/programs/prog4.png',
                        'reg_start_date' => '2026-07-10',
                        'reg_end_date' => '2026-08-05',
                        'fee' => 190,
                        'min_payment' => 200,
                        'discount_percent' => 10,
                        'google_tags' => ['GoogleClassroom', 'GoogleMeet', 'GoogleDocs'],
                    ],
                ],
            ],
        ];

        foreach ($programs as $index => $data) {
            $subjects = $data['subjects'];
            unset($data['subjects']);

            $program = Program::updateOrCreate(['slug' => $data['slug']], $data);

            foreach ($subjects as $subjectOrder => $subjectData) {
                $subjectData['sort_order'] = $subjectOrder + 1;
                $program->subjects()->updateOrCreate(
                    ['name_en' => $subjectData['name_en']],
                    $subjectData
                );
            }
        }
    }
}
