<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Team;
use App\Models\TeamTranslation;
use Illuminate\Support\Facades\DB;

class TeamsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Team::truncate();
        TeamTranslation::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $teams = [
            [
                'image' => 'site/images/img/students/teacher_1.png',
                'en' => [
                    'name' => 'Ahmad Al-Saeed',
                    'address1' => 'IELTS Expert',
                    'description' => 'Certified British Council trainer.'
                ],
                'ar' => [
                    'name' => 'أحمد السعيد',
                    'address1' => 'خبير آيلتس',
                    'description' => 'مدرب معتمد من المجلس الثقافي البريطاني.'
                ]
            ],
            [
                'image' => 'site/images/img/students/teacher_2.png',
                'en' => [
                    'name' => 'Mariam Naser',
                    'address1' => 'General English',
                    'description' => 'Specialist in communicative language.'
                ],
                'ar' => [
                    'name' => 'مريم ناصر',
                    'address1' => 'لغة إنجليزية عامة',
                    'description' => 'متخصصة في مهارات التواصل والمحادثة.'
                ]
            ],
            [
                'image' => 'site/images/img/students/teacher_3.png',
                'en' => [
                    'name' => 'Dr. Omar Fayed',
                    'address1' => 'Tawjihi Coordinator',
                    'description' => 'Ensuring top academic scores.'
                ],
                'ar' => [
                    'name' => 'د. عمر فايد',
                    'address1' => 'منسق التوجيهي',
                    'description' => 'ضمان الحصول على أعلى العلامات الأكاديمية.'
                ]
            ],
            [
                'image' => 'site/images/img/students/teacher_4.png',
                'en' => [
                    'name' => 'Sarah Jones',
                    'address1' => 'Native Speaker',
                    'description' => 'Focus on advanced conversation.'
                ],
                'ar' => [
                    'name' => 'سارة جونز',
                    'address1' => 'متحدثة أصلية',
                    'description' => 'التركيز على مهارات المحادثة المتقدمة.'
                ]
            ]
        ];

        foreach ($teams as $idx => $teamData) {
            $team = Team::create([
                'image' => $teamData['image'],
                'status' => 1,
                'display_order' => $idx,
                'member_type' => 1, // assuming 1 is regular member
                'is_chairman' => 0
            ]);

            foreach (['en', 'ar'] as $locale) {
                TeamTranslation::create(array_merge([
                    'team_id' => $team->id,
                    'locale' => $locale
                ], $teamData[$locale]));
            }
        }
    }
}
