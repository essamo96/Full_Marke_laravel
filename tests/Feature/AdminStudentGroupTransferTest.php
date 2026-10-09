<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Exam;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\PaymentMethod;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentExamGrant;
use App\Models\Subject;
use App\Services\StudentGroupTransferService;
use App\Services\TeacherFinanceReport;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class AdminStudentGroupTransferTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_transfer_options_list_every_same_subject_group_except_the_current_one(): void
    {
        [$admin, $registration, $current, $sameA, $sameBInactive, $otherSubjectGroup] = $this->makeTransferScene();

        $response = $this->actingAs($admin, 'admin')
            ->getJson(route('groups.students.transfer-options', $registration->id));

        $response->assertOk()->assertJsonPath('success', true);

        $ids = collect($response->json('groups'))->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertEqualsCanonicalizing([$sameA->id, $sameBInactive->id], $ids);
        $this->assertNotContains($current->id, $ids);
        $this->assertNotContains($otherSubjectGroup->id, $ids);
        $this->assertNotEmpty(collect($response->json('groups'))->firstWhere('id', $sameBInactive->id)['label'] ?? null);
    }

    public function test_transfer_options_accept_an_encrypted_registration_id(): void
    {
        [$admin, $registration] = $this->makeTransferScene();

        $this->actingAs($admin, 'admin')
            ->getJson(route('groups.students.transfer-options', Crypt::encrypt($registration->id)))
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_transfer_moves_registration_payments_and_submitted_grades_to_the_new_group(): void
    {
        [$admin, $registration, $current, $destination] = $this->makeTransferScene();
        $student = $registration->student;

        $exam = Exam::create([
            'subject_id' => $registration->subject_id,
            'group_id' => $current->id,
            'title' => 'امتحان المجموعة السابقة',
            'status' => 'published',
            'audience' => 'students',
            'start_time' => now()->subHour(),
            'end_time' => now()->addDay(),
            'duration_minutes' => 30,
        ]);

        $grade = Grade::create([
            'student_id' => $student->id,
            'group_id' => $current->id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->title,
            'score' => 12,
            'max_score' => 20,
        ]);

        $oldCount = $current->current_count;
        $newCount = $destination->current_count;

        $response = $this->actingAs($admin, 'admin')->postJson(route('groups.students.transfer'), [
            'registration_id' => $registration->id,
            'group_id' => $destination->id,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $registration->refresh();
        $grade->refresh();
        $current->refresh();
        $destination->refresh();

        $this->assertSame($destination->id, (int) $registration->group_id);
        $this->assertSame($destination->id, (int) $grade->group_id);
        $this->assertSame($exam->id, (int) $grade->exam_id);

        $this->assertTrue(
            PaymentItem::query()
                ->where('registration_id', $registration->id)
                ->exists()
        );

        $this->assertTrue(
            StudentExamGrant::query()
                ->where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->exists()
        );

        $this->assertSame(max(0, $oldCount - 1), (int) $current->current_count);
        $this->assertSame($newCount + 1, (int) $destination->current_count);

        $newTeacherReport = TeacherFinanceReport::for($destination->teacher);
        $oldTeacherReport = TeacherFinanceReport::for($current->teacher);

        $this->assertTrue(
            $newTeacherReport->registrationsFor(collect([$destination->id]))
                ->contains(fn ($row) => (int) $row->id === (int) $registration->id)
        );
        $this->assertFalse(
            $oldTeacherReport->registrationsFor(collect([$current->id]))
                ->contains(fn ($row) => (int) $row->id === (int) $registration->id)
        );
    }

    public function test_transfer_rejects_a_group_from_another_subject(): void
    {
        [$admin, $registration, , , , $otherSubjectGroup] = $this->makeTransferScene();

        $this->actingAs($admin, 'admin')->postJson(route('groups.students.transfer'), [
            'registration_id' => $registration->id,
            'group_id' => $otherSubjectGroup->id,
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_service_is_a_noop_rejection_when_already_in_the_target_group(): void
    {
        [, $registration, $current] = $this->makeTransferScene();

        $this->expectException(\InvalidArgumentException::class);
        app(StudentGroupTransferService::class)->transfer($registration, $current);
    }

    /**
     * @return array{0: Admin, 1: Registration, 2: Group, 3: Group, 4: Group, 5: Group}
     */
    private function makeTransferScene(): array
    {
        $subject = Subject::factory()->create();
        $otherSubject = Subject::factory()->create();

        $current = Group::factory()->create([
            'subject_id' => $subject->id,
            'name' => 'المجموعة الحالية',
            'current_count' => 1,
            'is_active' => true,
        ]);
        $sameA = Group::factory()->create([
            'subject_id' => $subject->id,
            'name' => 'مجموعة أخرى فعالة',
            'current_count' => 0,
            'is_active' => true,
        ]);
        $sameBInactive = Group::factory()->create([
            'subject_id' => $subject->id,
            'name' => 'مجموعة غير فعالة',
            'current_count' => 0,
            'is_active' => false,
        ]);
        $otherSubjectGroup = Group::factory()->create([
            'subject_id' => $otherSubject->id,
            'name' => 'مجموعة مادة أخرى',
            'is_active' => true,
        ]);

        $student = Student::factory()->create();
        $registration = Registration::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $current->id,
            'status' => 'fully_paid',
            'fee_snapshot' => 100,
            'amount_paid' => 100,
        ]);

        $method = PaymentMethod::query()->first() ?? PaymentMethod::create([
            'name_ar' => 'بنكي',
            'name_en' => 'Bank',
            'is_active' => 1,
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-TR-' . $student->id,
            'student_id' => $student->id,
            'method' => $method->id,
            'amount' => 100,
            'status' => 'confirmed',
            'receipt_image' => 'receipt.jpg',
        ]);

        PaymentItem::create([
            'payment_id' => $payment->id,
            'registration_id' => $registration->id,
            'allocated_amount' => 100,
        ]);

        $admin = Admin::query()->first();
        if (! $admin) {
            $admin = Admin::create([
                'name' => 'Transfer Admin',
                'email' => 'transfer-admin-'.uniqid().'@example.test',
                'password' => 'secret-pass-123',
                'status' => 1,
            ]);
        }

        return [$admin, $registration, $current, $sameA, $sameBInactive, $otherSubjectGroup];
    }
}
