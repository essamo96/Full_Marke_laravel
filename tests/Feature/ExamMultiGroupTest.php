<?php

namespace Tests\Feature;

use App\Jobs\NotifyStudentsExamStarting;
use App\Jobs\NotifyStudentsOfNewExam;
use App\Models\Admin;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\ExamStartingNowNotification;
use App\Notifications\NewExamPublishedNotification;
use App\Services\ExamAudience;
use App\Services\ExamNotifier;
use App\Services\ExamService;
use App\Services\StudentExamGrantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\BuildsExamScenario;
use Tests\TestCase;

/**
 * One exam published to several groups at once, with per-student exclusions,
 * and the live notification reaching exactly the right students.
 */
class ExamMultiGroupTest extends TestCase
{
    use BuildsExamScenario, DatabaseTransactions;

    private function examPayload($subject, array $groupIds, array $extra = []): array
    {
        return array_merge([
            'subject_id' => $subject->id,
            'group_ids' => $groupIds,
            'title' => 'امتحان متعدد المجموعات',
            'start_time' => now()->format('Y-m-d H:i'),
            'end_time' => now()->addHour()->format('Y-m-d H:i'),
            'duration_minutes' => 30,
            'status' => 'published',
            'audience' => 'students',
        ], $extra);
    }

    public function test_exam_can_be_saved_for_several_groups_and_keeps_a_primary_group(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();

        $exam = app(ExamService::class)->saveExam($this->examPayload($subject, [$a->id, $b->id]));

        $this->assertEqualsCanonicalizing([$a->id, $b->id], $exam->allGroupIds());
        $this->assertSame($a->id, $exam->group_id, 'first selected group stays the primary exams.group_id');
        $this->assertDatabaseHas('exam_group', ['exam_id' => $exam->id, 'group_id' => $b->id]);
    }

    public function test_updating_groups_syncs_the_pivot_and_exclusions_can_be_cleared(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $student = $this->makeStudent();
        $service = app(ExamService::class);

        $exam = $service->saveExam($this->examPayload($subject, [$a->id, $b->id], ['excluded_student_ids' => [(string) $student->id]]));
        $this->assertSame([$student->id], $exam->excludedStudentIds(), 'string ids are normalised to ints');
        $this->assertSame([$student->id], $exam->fresh()->excluded_student_ids);

        // form re-submitted with only group B and nobody excluded (no checkbox ticked -> only the marker is posted)
        $exam = $service->saveExam($this->examPayload($subject, [$b->id], ['exclusions_present' => '1']), $exam);

        $this->assertSame([$b->id], $exam->allGroupIds());
        $this->assertSame($b->id, $exam->group_id);
        $this->assertNull($exam->fresh()->excluded_student_ids);
    }

    public function test_exclusions_are_kept_when_the_form_does_not_send_them(): void
    {
        [$subject, , $a] = $this->makeSubjectWithTwoGroups();
        $student = $this->makeStudent();
        $service = app(ExamService::class);

        $exam = $service->saveExam($this->examPayload($subject, [$a->id], ['excluded_student_ids' => [$student->id]]));
        $exam = $service->saveExam($this->examPayload($subject, [$a->id]), $exam);

        $this->assertSame([$student->id], $exam->fresh()->excludedStudentIds());
    }

    public function test_audience_is_every_student_of_every_group_once_minus_exclusions(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $inA = $this->makeStudent();
        $inA2 = $this->makeStudent();
        $inB = $this->makeStudent();
        $excluded = $this->makeStudent();
        $cancelled = $this->makeStudent();
        $outsider = $this->makeStudent();

        $this->enroll($inA, $a);
        $this->enroll($inA2, $a, 'pending');
        $this->enroll($inB, $b, 'partially_paid');
        $this->enroll($excluded, $b);
        $this->enroll($cancelled, $a)->update(['status' => null]); // no valid registration status -> not part of the audience
        $this->enroll($outsider, Group::factory()->create(['subject_id' => $subject->id]));

        $exam = $this->makeExam($subject, [$a, $b], ['excluded_student_ids' => [(string) $excluded->id]]);

        $ids = app(ExamAudience::class)->students($exam)->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$inA->id, $inA2->id, $inB->id], $ids);
        $this->assertEqualsCanonicalizing([$inB->id], app(ExamAudience::class)->students($exam, [$b->id])->pluck('id')->all());
    }

    public function test_guest_only_exam_has_no_student_audience(): void
    {
        [$subject, , $a] = $this->makeSubjectWithTwoGroups();
        $this->enroll($this->makeStudent(), $a);

        $exam = $this->makeExam($subject, [$a], ['audience' => 'guests']);

        $this->assertCount(0, app(ExamAudience::class)->students($exam));
    }

    public function test_new_exam_notification_reaches_all_selected_groups_but_not_excluded_students(): void
    {
        Notification::fake();
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $studentA = $this->makeStudent();
        $studentB = $this->makeStudent();
        $excluded = $this->makeStudent();
        $otherGroupStudent = $this->makeStudent();
        $this->enroll($studentA, $a);
        $this->enroll($studentB, $b);
        $this->enroll($excluded, $a);
        $this->enroll($otherGroupStudent, Group::factory()->create(['subject_id' => $subject->id]));

        $exam = $this->makeExam($subject, [$a, $b], ['excluded_student_ids' => [$excluded->id]]);

        (new NotifyStudentsOfNewExam($exam))->handle(app(ExamAudience::class));

        Notification::assertSentTo($studentA, NewExamPublishedNotification::class);
        Notification::assertSentTo($studentB, NewExamPublishedNotification::class);
        Notification::assertNotSentTo($excluded, NewExamPublishedNotification::class);
        Notification::assertNotSentTo($otherGroupStudent, NewExamPublishedNotification::class);
        Notification::assertCount(2);
    }

    public function test_exam_starting_notification_also_covers_every_group(): void
    {
        Notification::fake();
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $studentA = $this->makeStudent();
        $studentB = $this->makeStudent();
        $this->enroll($studentA, $a);
        $this->enroll($studentB, $b);

        $exam = $this->makeExam($subject, [$a, $b]);

        (new NotifyStudentsExamStarting($exam))->handle(app(ExamAudience::class));

        Notification::assertSentTo($studentA, ExamStartingNowNotification::class);
        Notification::assertSentTo($studentB, ExamStartingNowNotification::class);
    }

    public function test_one_failing_push_does_not_stop_the_other_students_from_being_notified(): void
    {
        [$subject, , $a] = $this->makeSubjectWithTwoGroups();
        $first = $this->makeStudent();
        $second = $this->makeStudent();
        $third = $this->makeStudent();
        foreach ([$first, $second, $third] as $s) {
            $this->enroll($s, $a);
        }
        $exam = $this->makeExam($subject, [$a]);

        // Simulate a dead push for exactly one student (the sender throws before delivering).
        $broken = $second->id;
        Event::listen(NotificationSending::class, function ($event) use ($broken) {
            if ($event->notifiable->id === $broken) {
                throw new \RuntimeException('websocket down');
            }
        });

        (new NotifyStudentsOfNewExam($exam))->handle(app(ExamAudience::class));

        $notified = DB::table('notifications')
            ->where('type', NewExamPublishedNotification::class)
            ->where('notifiable_type', Student::class)
            ->pluck('notifiable_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertContains($first->id, $notified);
        $this->assertContains($third->id, $notified, 'students after the broken one are still notified');
        $this->assertNotContains($second->id, $notified);
    }

    public function test_publishing_dispatches_one_job_and_adding_a_group_notifies_only_the_new_group(): void
    {
        Queue::fake();
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $notifier = app(ExamNotifier::class);

        $draft = $this->makeExam($subject, [$a], ['status' => 'draft']);
        $notifier->afterSave($draft, null);
        Queue::assertNothingPushed(); // drafts are silent

        $published = $this->makeExam($subject, [$a]);
        $notifier->afterSave($published, 'draft', [$a->id]);
        Queue::assertPushed(NotifyStudentsOfNewExam::class, fn ($job) => $job->onlyGroupIds === null);

        Queue::fake();
        $published->groups()->sync([$a->id, $b->id]);
        $notifier->afterSave($published->fresh(), 'published', [$a->id]);
        Queue::assertPushed(NotifyStudentsOfNewExam::class, fn ($job) => $job->onlyGroupIds === [$b->id]);

        Queue::fake();
        $notifier->afterSave($published->fresh(), 'published', [$a->id, $b->id]); // nothing changed
        Queue::assertNothingPushed();
    }

    public function test_adding_a_group_to_a_published_exam_only_alerts_the_new_groups_students(): void
    {
        Notification::fake();
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $existing = $this->makeStudent();
        $newcomer = $this->makeStudent();
        $this->enroll($existing, $a);
        $this->enroll($newcomer, $b);

        $exam = $this->makeExam($subject, [$a, $b]);

        (new NotifyStudentsOfNewExam($exam, [$b->id]))->handle(app(ExamAudience::class));

        Notification::assertSentTo($newcomer, NewExamPublishedNotification::class);
        Notification::assertNotSentTo($existing, NewExamPublishedNotification::class);
    }

    public function test_student_of_any_selected_group_can_open_the_exam_and_excluded_or_foreign_students_cannot(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $inB = $this->makeStudent();
        $excluded = $this->makeStudent();
        $foreign = $this->makeStudent();
        $exam = $this->makeExam($subject, [$a, $b], ['excluded_student_ids' => [(string) $excluded->id]]);
        $service = app(StudentExamGrantService::class);

        $this->assertTrue($service->studentCanAccessExam($exam, $inB->id, [$b->id]));
        $this->assertFalse($service->studentCanAccessExam($exam, $excluded->id, [$b->id]), 'exclusion wins over group membership');
        $this->assertFalse($service->studentCanAccessExam($exam, $foreign->id, [Group::factory()->create()->id]));
    }

    public function test_exam_list_shows_multi_group_exam_to_second_group_and_hides_it_from_excluded_student(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $inB = $this->makeStudent();
        $excluded = $this->makeStudent();
        $this->enroll($inB, $b);
        $this->enroll($excluded, $b);
        $exam = $this->makeExam($subject, [$a, $b], ['excluded_student_ids' => [(string) $excluded->id], 'title' => 'امتحان مشترك فريد']);

        $this->actingAs($inB, 'student')->get(route('student.exams.index'))
            ->assertOk()->assertSee('امتحان مشترك فريد');

        $this->actingAs($excluded, 'student')->get(route('student.exams.index'))
            ->assertOk()->assertDontSee('امتحان مشترك فريد');
    }

    public function test_deleting_the_primary_group_does_not_destroy_an_exam_shared_with_other_groups(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $shared = $this->makeExam($subject, [$a, $b]);
        $soloA = $this->makeExam($subject, [$a], ['title' => 'خاص بالمجموعة أ']);

        $a->delete();

        $this->assertDatabaseHas('exams', ['id' => $shared->id, 'group_id' => $b->id]);
        $this->assertSame([$b->id], $shared->fresh()->allGroupIds());
        // an exam that only ever belonged to the deleted group still goes with it, as before
        $this->assertDatabaseMissing('exams', ['id' => $soloA->id]);
    }

    public function test_exams_for_groups_scope_finds_exams_through_the_pivot(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $exam = $this->makeExam($subject, [$a, $b]);

        $this->assertTrue(Exam::forGroups([$b->id])->whereKey($exam->id)->exists());
        $this->assertTrue(Exam::forGroups([$a->id])->whereKey($exam->id)->exists());
        $this->assertFalse(Exam::forGroups([Group::factory()->create()->id])->whereKey($exam->id)->exists());
    }

    public function test_teacher_can_publish_one_exam_to_several_own_groups_but_not_to_a_colleagues_group(): void
    {
        Queue::fake();
        [$subject, $teacher, $a, $b] = $this->makeSubjectWithTwoGroups();
        $colleagueGroup = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => Teacher::factory()->create()->id]);
        $payload = fn (array $groups) => $this->examPayload($subject, $groups, [
            'questions' => [[
                'type' => 'multiple_choice', 'content' => 'سؤال', 'points' => 1,
                'options' => [
                    ['option_text' => 'أ', 'is_correct' => 1],
                    ['option_text' => 'ب', 'is_correct' => 0],
                ],
            ]],
        ]);

        $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.exams.store'), $payload([$a->id, $b->id]))
            ->assertRedirect(route('teacher.exams.index'));

        $exam = Exam::where('title', 'امتحان متعدد المجموعات')->latest('id')->firstOrFail();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $exam->allGroupIds());
        Queue::assertPushed(NotifyStudentsOfNewExam::class);

        $this->actingAs($teacher, 'teacher')
            ->post(route('teacher.exams.store'), $payload([$a->id, $colleagueGroup->id]))
            ->assertForbidden();
    }

    public function test_teacher_pages_render_for_a_multi_group_exam(): void
    {
        [$subject, $teacher, $a, $b] = $this->makeSubjectWithTwoGroups();
        $student = $this->makeStudent();
        $this->enroll($student, $b);
        $exam = $this->makeExam($subject, [$a, $b]);

        $this->actingAs($teacher, 'teacher');
        $this->get(route('teacher.exams.index'))->assertOk()->assertSee('مجموعة أ، مجموعة ب', false);
        $this->get(route('teacher.exams.create'))->assertOk();
        $this->get(route('teacher.exams.edit', $exam))->assertOk();
        $this->get(route('teacher.exams.preview', $exam))->assertOk()->assertSee('مجموعة أ');
        $this->get(route('teacher.grading.exam', $exam))->assertOk()->assertSee($student->full_name_ar);
        $this->get(route('teacher.exams.ajax.groups-students', ['group_ids' => [$a->id, $b->id]]))
            ->assertOk()->assertJsonFragment(['id' => $student->id, 'groups' => 'مجموعة ب']);
    }

    private function adminUser(): Admin
    {
        return Admin::query()->first() ?? Admin::create([
            'name' => 'Exam Admin',
            'email' => 'exam-admin-'.uniqid().'@example.test',
            'password' => 'secret-pass-123',
            'status' => 1,
        ]);
    }

    public function test_admin_publishes_to_several_groups_and_every_included_student_is_alerted_at_once(): void
    {
        Notification::fake();
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $studentA = $this->makeStudent();
        $studentB = $this->makeStudent();
        $excluded = $this->makeStudent();
        $this->enroll($studentA, $a);
        $this->enroll($studentB, $b);
        $this->enroll($excluded, $b);

        $this->actingAs($this->adminUser(), 'admin')
            ->post(route('exams.store'), $this->examPayload($subject, [$a->id, $b->id], [
                'excluded_student_ids' => [(string) $excluded->id],
                'exclusions_present' => '1',
                'questions' => [[
                    'type' => 'multiple_choice', 'content' => 'سؤال', 'points' => 1,
                    'options' => [['option_text' => 'أ', 'is_correct' => 1], ['option_text' => 'ب', 'is_correct' => 0]],
                ]],
            ]))
            ->assertRedirect(route('exams.view'));

        $exam = Exam::where('title', 'امتحان متعدد المجموعات')->latest('id')->firstOrFail();
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $exam->allGroupIds());
        Notification::assertSentTo($studentA, NewExamPublishedNotification::class);
        Notification::assertSentTo($studentB, NewExamPublishedNotification::class);
        Notification::assertNotSentTo($excluded, NewExamPublishedNotification::class);
    }

    public function test_admin_pages_and_students_picker_work_for_multi_group_exams(): void
    {
        [$subject, , $a, $b] = $this->makeSubjectWithTwoGroups();
        $student = $this->makeStudent();
        $this->enroll($student, $a);
        $exam = $this->makeExam($subject, [$a, $b]);

        $this->actingAs($this->adminUser(), 'admin');
        $this->get(route('exams.view'))->assertOk()->assertSee('مجموعة أ، مجموعة ب');
        $this->get(route('exams.create'))->assertOk();
        $this->get(route('exams.edit', $exam))->assertOk();
        $this->get(route('exams.results', $exam))->assertOk();
        $this->get(route('exams.ajax.groups-students', ['group_ids' => [$a->id, $b->id]]))
            ->assertOk()->assertJsonFragment(['id' => $student->id]);
    }

    public function test_students_picker_refuses_groups_of_other_teachers(): void
    {
        [, $teacher, $a] = $this->makeSubjectWithTwoGroups();
        $foreign = Group::factory()->create();

        $this->actingAs($teacher, 'teacher')
            ->get(route('teacher.exams.ajax.groups-students', ['group_ids' => [$a->id, $foreign->id]]))
            ->assertForbidden();
    }
}
