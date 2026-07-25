<?php

namespace Database\Seeders;

use App\Models\PosPoint;
use Illuminate\Database\Seeder;

class PosPointsSeeder extends Seeder
{
    public function run(): void
    {
        $points = [
            [
                'name_ar' => 'مكتبة العلامة الكاملة - خانيونس',
                'name_en' => 'Full Mark Bookshop - Khan Yunis',
                'image' => 'pos_points/seed_1.jpg',
                'address_ar' => 'خانيونس - شارع الاستقلال - بجانب صيدلية الأمل',
                'address_en' => 'Khan Yunis - Al-Istiqlal Street - next to Al-Amal Pharmacy',
                'working_hours_ar' => 'يومياً من 9 صباحاً حتى 9 مساءً',
                'working_hours_en' => 'Daily 9 AM - 9 PM',
                'booklet_price' => 12.50,
                'phone' => '00970566959697',
                'latitude' => 31.3469,
                'longitude' => 34.3029,
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'name_ar' => 'مكتبة العلامة الكاملة - غزة',
                'name_en' => 'Full Mark Bookshop - Gaza City',
                'image' => 'pos_points/seed_2.jpg',
                'address_ar' => 'غزة - شارع عمر المختار - مقابل بلدية غزة',
                'address_en' => 'Gaza City - Omar Al-Mukhtar Street - opposite Gaza Municipality',
                'working_hours_ar' => 'يومياً من 8:30 صباحاً حتى 8:30 مساءً',
                'working_hours_en' => 'Daily 8:30 AM - 8:30 PM',
                'booklet_price' => 13.00,
                'phone' => '00970599123456',
                'latitude' => 31.5017,
                'longitude' => 34.4668,
                'sort_order' => 2,
                'is_active' => 1,
            ],
            [
                'name_ar' => 'مكتبة العلامة الكاملة - رفح',
                'name_en' => 'Full Mark Bookshop - Rafah',
                'image' => 'pos_points/seed_3.jpg',
                'address_ar' => 'رفح - شارع النصر - بالقرب من المسجد الكبير',
                'address_en' => 'Rafah - Al-Nasr Street - near the Grand Mosque',
                'working_hours_ar' => 'يومياً من 9 صباحاً حتى 8 مساءً',
                'working_hours_en' => 'Daily 9 AM - 8 PM',
                'booklet_price' => 12.00,
                'phone' => '00970598765432',
                'latitude' => 31.2966,
                'longitude' => 34.2436,
                'sort_order' => 3,
                'is_active' => 1,
            ],
            [
                'name_ar' => 'مكتبة العلامة الكاملة - دير البلح',
                'name_en' => 'Full Mark Bookshop - Deir al-Balah',
                'image' => 'pos_points/seed_4.jpg',
                'address_ar' => 'دير البلح - الشارع الرئيسي - بجانب مستشفى شهداء الأقصى',
                'address_en' => 'Deir al-Balah - Main Street - next to Al-Aqsa Martyrs Hospital',
                'working_hours_ar' => 'يومياً من 9 صباحاً حتى 7:30 مساءً',
                'working_hours_en' => 'Daily 9 AM - 7:30 PM',
                'booklet_price' => 12.75,
                'phone' => '00970597654321',
                'latitude' => 31.4181,
                'longitude' => 34.3517,
                'sort_order' => 4,
                'is_active' => 1,
            ],
            [
                'name_ar' => 'مكتبة العلامة الكاملة - جباليا',
                'name_en' => 'Full Mark Bookshop - Jabalia',
                'image' => 'pos_points/seed_5.jpg',
                'address_ar' => 'جباليا - شارع السوق - مقابل مخيم جباليا',
                'address_en' => 'Jabalia - Al-Souq Street - opposite Jabalia Camp',
                'working_hours_ar' => 'يومياً من 9 صباحاً حتى 8 مساءً',
                'working_hours_en' => 'Daily 9 AM - 8 PM',
                'booklet_price' => 13.25,
                'phone' => '00970596543210',
                'latitude' => 31.5272,
                'longitude' => 34.4831,
                'sort_order' => 5,
                'is_active' => 1,
            ],
        ];

        foreach ($points as $point) {
            PosPoint::updateOrCreate(
                ['name_ar' => $point['name_ar']],
                $point
            );
        }
    }
}
