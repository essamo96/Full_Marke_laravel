<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Social;

class SocialsSeeder extends Seeder
{
    public function run(): void
    {
        $socials = [
            ['name_ar' => 'فيسبوك', 'name_en' => 'Facebook', 'link' => 'https://facebook.com', 'icon' => 'bi-facebook', 'status' => 1],
            ['name_ar' => 'تويتر', 'name_en' => 'Twitter', 'link' => 'https://twitter.com', 'icon' => 'bi-twitter', 'status' => 1],
            ['name_ar' => 'انستجرام', 'name_en' => 'Instagram', 'link' => 'https://instagram.com', 'icon' => 'bi-instagram', 'status' => 1],
            ['name_ar' => 'يوتيوب', 'name_en' => 'YouTube', 'link' => 'https://youtube.com', 'icon' => 'bi-youtube', 'status' => 1],
        ];

        foreach ($socials as $social) {
            Social::firstOrCreate(
                ['name_en' => $social['name_en']],
                $social
            );
        }
    }
}
