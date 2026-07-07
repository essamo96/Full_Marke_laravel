<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use App\Models\FaqTranslation;
use Illuminate\Support\Facades\DB;

class FaqsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Faq::truncate();
        FaqTranslation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $faqs = [
            [
                'en' => [
                    'question' => 'What is the Full Mark Test of English (OTE)?',
                    'answer' => 'The Full Mark Test of English (OTE) is a multi-level general English proficiency test certified by the Full Mark University. It assesses Listening, Speaking, Reading, and Writing skills at levels A2, B1, and B2 of the CEFR.'
                ],
                'ar' => [
                    'question' => 'ما هو اختبار العلامة الكاملة للغة الإنجليزية (OTE)؟',
                    'answer' => 'اختبار العلامة الكاملة للغة الإنجليزية (OTE) هو اختبار كفاءة لغوي متعدد المستويات معتمد من جامعة العلامة الكاملة. يقيم مهارات الاستماع والمحادثة والقراءة والكتابة للمستويات A2 و B1 و B2 في الإطار الأوروبي المشترك.'
                ]
            ],
            [
                'en' => [
                    'question' => 'How can I book a placement test?',
                    'answer' => 'You can easily book a placement test online by filling out our quick contact form, choosing \'Placement Test\' in the Contact Type dropdown, or contacting us via our direct phone line.'
                ],
                'ar' => [
                    'question' => 'كيف يمكنني حجز اختبار تحديد المستوى؟',
                    'answer' => 'يمكنك حجز موعد اختبار تحديد المستوى مباشرة عبر الإنترنت من خلال ملء نموذج الاتصال بنا وتحديد \'تحديد المستوى\' من قائمة نوع الطلب، أو من خلال الاتصال المباشر بخدمة العملاء.'
                ]
            ],
            [
                'en' => [
                    'question' => 'Are your certificates internationally recognized?',
                    'answer' => 'Yes, FULL MARKS ACADEMY is a registered test centre. The Full Mark Test of English certificate is officially approved by embassies, universities, and international organizations globally.'
                ],
                'ar' => [
                    'question' => 'هل الشهادات الصادرة معترف بها دولياً؟',
                    'answer' => 'نعم، أكاديمية العلامة الكاملة هي مركز اختبارات مسجل ومعتمد رسميًا. شهادة اختبار العلامة الكاملة معترف بها ومصادق عليها من قبل السفارات والجامعات والمؤسسات الدولية حول العالم.'
                ]
            ]
        ];

        foreach ($faqs as $faqData) {
            $faq = Faq::create([
                'status' => 1
            ]);

            foreach (['en', 'ar'] as $locale) {
                FaqTranslation::create([
                    'faq_id' => $faq->id,
                    'locale' => $locale,
                    'question' => $faqData[$locale]['question'],
                    'answer' => $faqData[$locale]['answer'],
                    'title' => '',
                    'description' => ''
                ]);
            }
        }
    }
}
