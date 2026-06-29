<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (Branch::count() === 0) {
            Branch::factory()->count(3)->create();
        }

        $branchIds = Branch::pluck('id');

        Student::factory()
            ->count(20)
            ->create(['branch_id' => fn () => $branchIds->random()]);

        Student::factory()
            ->inactive()
            ->count(3)
            ->create(['branch_id' => fn () => $branchIds->random()]);
    }
}
