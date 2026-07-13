<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;

class DeleteUnverifiedStudentsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_deletes_unverified_students_older_than_24_hours()
    {
        // Verified student older than 24 hours (should keep)
        $verified = Student::factory()->create([
            'email_verified_at' => now(),
            'created_at' => now()->subHours(25)
        ]);

        // Unverified student newer than 24 hours (should keep)
        $unverifiedNew = Student::factory()->create([
            'email_verified_at' => null,
            'created_at' => now()->subHours(20)
        ]);

        // Unverified student older than 24 hours (should delete)
        $unverifiedOld = Student::factory()->create([
            'email_verified_at' => null,
            'created_at' => now()->subHours(25)
        ]);

        Artisan::call('students:delete-unverified');

        $this->assertDatabaseHas('students', ['id' => $verified->id]);
        $this->assertDatabaseHas('students', ['id' => $unverifiedNew->id]);
        $this->assertDatabaseMissing('students', ['id' => $unverifiedOld->id]);
    }
}
