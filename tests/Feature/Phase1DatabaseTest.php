<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Program;
use App\Models\Subject;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Registration;
use App\Models\Payment;
use App\Models\PaymentRegistration;
use App\Models\PaymentStatusLog;
use App\Models\EmailVerificationCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class Phase1DatabaseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_creates_full_chain_of_records()
    {
        // 1. Program
        $program = Program::factory()->create(['name_ar' => 'برنامج التوجيهي']);
        $this->assertDatabaseHas('programs', ['name_ar' => 'برنامج التوجيهي']);

        // 2. Subject
        $subject = Subject::factory()->create(['program_id' => $program->id, 'name_ar' => 'فيزياء']);
        $this->assertDatabaseHas('subjects', ['name_ar' => 'فيزياء', 'program_id' => $program->id]);

        // 3. Teacher
        $teacher = Teacher::factory()->create(['name' => 'أ. أحمد']);
        $this->assertDatabaseHas('teachers', ['name' => 'أ. أحمد']);

        // 4. Group
        $group = Group::factory()->create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'name' => 'مجموعة 1',
        ]);
        $this->assertDatabaseHas('groups', ['name' => 'مجموعة 1', 'teacher_id' => $teacher->id]);

        // 5. Guardian
        $guardian = Guardian::factory()->create(['name' => 'ولي أمر 1']);
        $this->assertDatabaseHas('guardians', ['name' => 'ولي أمر 1']);

        // 6. Student
        $student = Student::factory()->create(['guardian_id' => $guardian->id, 'full_name_ar' => 'طالب 1']);
        $this->assertDatabaseHas('students', ['full_name_ar' => 'طالب 1', 'guardian_id' => $guardian->id]);

        // 7. Email Verification Code
        $code = EmailVerificationCode::factory()->create(['student_id' => $student->id]);
        $this->assertDatabaseHas('email_verification_codes', ['student_id' => $student->id]);

        // 8. Registration
        $registration = Registration::factory()->create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'subject_id' => $subject->id,
            'fee_snapshot' => 150.00,
        ]);
        $this->assertDatabaseHas('registrations', ['student_id' => $student->id, 'fee_snapshot' => 150.00]);

        // 9. Payment
        $payment = Payment::factory()->create(['student_id' => $student->id, 'amount' => 100.00]);
        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'amount' => 100.00]);

        // 10. Payment Registration
        PaymentRegistration::factory()->create([
            'payment_id' => $payment->id,
            'registration_id' => $registration->id,
            'allocated_amount' => 100.00,
        ]);
        $this->assertDatabaseHas('payment_registrations', ['payment_id' => $payment->id, 'allocated_amount' => 100.00]);

        // 11. Payment Status Log
        PaymentStatusLog::factory()->create([
            'payment_id' => $payment->id,
            'action' => 'approved',
        ]);
        $this->assertDatabaseHas('payment_status_logs', ['payment_id' => $payment->id, 'action' => 'approved']);

        // Test Relationships
        $this->assertEquals(1, $program->subjects->count());
        $this->assertEquals(1, $subject->groups->count());
        $this->assertEquals(1, $teacher->groups->count());
        $this->assertEquals(1, $guardian->students->count());
        $this->assertEquals(1, $student->registrations->count());
        $this->assertEquals(1, $student->emailVerificationCodes->count());
        $this->assertEquals(1, $registration->paymentRegistrations->count());
        $this->assertEquals(1, $payment->paymentRegistrations->count());
        $this->assertEquals(1, $payment->statusLogs->count());
    }
}
