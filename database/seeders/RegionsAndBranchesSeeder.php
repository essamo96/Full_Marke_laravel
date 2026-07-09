<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Region;
use App\Models\Branch;

class RegionsAndBranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['name_ar' => 'غزة المدينة', 'name_en' => 'Gaza City', 'status' => 1],
            ['name_ar' => 'دير البلح', 'name_en' => 'Diralbalah', 'status' => 1],
            ['name_ar' => 'خانيونس', 'name_en' => 'Khan Younis', 'status' => 1],
        ];

        foreach ($regions as $region) {
            Region::firstOrCreate(
                ['name_ar' => $region['name_ar']],
                $region
            );
        }

        $branches = [
            ['name_ar' => 'الفرع الادبي', 'name_en' => 'Literary branch', 'status' => 1],
            ['name_ar' => 'فرع الشريعة', 'name_en' => 'Sharia branch', 'status' => 1],
            ['name_ar' => 'الفرع العلمي', 'name_en' => 'Scientific branch', 'status' => 1],
        ];

        foreach ($branches as $branch) {
            Branch::firstOrCreate(
                ['name_ar' => $branch['name_ar']],
                $branch
            );
        }
    }
}
