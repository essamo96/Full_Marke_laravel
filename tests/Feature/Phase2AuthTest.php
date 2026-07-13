<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Student;
use App\Models\EmailVerificationCode;
use App\Models\Region;
use App\Models\Branch;

class Phase2AuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_registration_creates_unverified_account_and_code()
    {
        $region = Region::create(['name_ar' => 'Test', 'name_en' => 'Test', 'status' => 1]);
        $branch = Branch::create(['name_ar' => 'Test', 'name_en' => 'Test', 'status' => 1, 'region_id' => $region->id]);

        $response = $this->postJson(route('student.register.submit'), [
            'fullnameAr' => 'طالب جديد',
            'fullnameEn' => 'New Student',
            'email' => 'newstudent@example.com',
            'phone' => '123456789',
            'dob' => '2005-01-01',
            'gender' => 'male',
            'region_id' => (string) $region->id,
            'branch_id' => (string) $branch->id,
            'program' => 'tawjihi',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'terms' => 'on',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('students', [
            'email' => 'newstudent@example.com',
            'email_verified_at' => null,
            'status' => 0
        ]);

        $student = Student::where('email', 'newstudent@example.com')->first();
        $this->assertDatabaseHas('email_verification_codes', [
            'student_id' => $student->id
        ]);
    }

    public function test_student_can_verify_email()
    {
        $student = Student::factory()->create([
            'email_verified_at' => null
        ]);

        $code = EmailVerificationCode::factory()->create([
            'student_id' => $student->id,
            'code' => \Illuminate\Support\Facades\Hash::make('123456'),
        ]);

        $response = $this->postJson(route('student.verify.submit'), [
            'email' => $student->email,
            'code' => '123456'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $this->assertDatabaseMissing('email_verification_codes', ['id' => $code->id]);
        $this->assertNotNull($student->fresh()->email_verified_at);
        $this->assertAuthenticatedAs($student, 'student');
    }

    public function test_unverified_student_cannot_access_programs()
    {
        $student = Student::factory()->create([
            'email_verified_at' => null
        ]);

        $program = \App\Models\Program::factory()->create();

        // Login as unverified student
        $this->actingAs($student, 'student');

        $response = $this->get(route('programs.show', $program->slug));

        $response->assertRedirect(route('apply.create'));
    }
}
