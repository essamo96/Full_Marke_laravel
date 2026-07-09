<?php

namespace Database\Seeders;

// use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Commented out for production as it uses Faker which is not available in --no-dev
        /*
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        */

        $this->call([
            AdminPermissionsSeeder::class,
            SiteSettingsSeeder::class,
            RegionsAndBranchesSeeder::class,
        ]);
        
        // Commented out for production to avoid dummy data and Faker errors
        // $this->call(StudentSeeder::class);
        
        $this->call(ProgramsSeeder::class);
        $this->call(TeamTestimonialSeeder::class);
        $this->call(NewsSeeder::class);
        $this->call(FaqSeeder::class);
    }
}
