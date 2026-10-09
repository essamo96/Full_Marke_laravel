<?php

namespace Tests\Feature;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Student;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsExamScenario;
use Tests\TestCase;

/**
 * Online exam reliability: answers are autosaved with per-question timestamps,
 * merged newest-wins, survive reloads / dropped connections, and the countdown
 * is owned by the server.
 */
class StudentExamAutosaveTest extends TestCase
{
    use BuildsExamScenario, DatabaseTransactions;

    private Student $student;

    private Exam $exam;

    private $groupA;

    private $groupB;

    protected function setUp(): void
    {
        parent::setUp();

        [$subject, , $this->groupA, $this->groupB] = $this->makeSubjectWithTwoGroups();
        $this->student = $this->makeStudent();
        $this->enroll($this->student, $this->groupB);
        $this->exam = $this->makeExam($subject, [$this->groupA, $this->groupB], [], 2, true);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function q(int $n)
    {
        return $this->exam->questions[$n];
    }

    private function correct(int $n): int
    {
        return $this->q($n)->options->firstWhere('is_correct', true)->id;
    }

    private function wrong(int $n): int
    {
        return $this->q($n)->options->firstWhere('is_correct', false)->id;
    }

    private function entry($value, int $t): array
    {
        return ['v' => $value, 't' => $t];
    }

    private function draft(array $answers, array $extra = [])
    {
        return $this->actingAs($this->student, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => $answers] + $extra);
    }

    private function storedAnswers(): array
    {
        return ExamAttempt::where(['exam_id' => $this->exam->id, 'student_id' => $this->student->id])->firstOrFail()->answers;
    }

    private function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }

    public function test_take_page_opens_an_attempt_with_the_full_time_and_no_answers(): void
    {
        $response = $this->actingAs($this->student, 'student')->get(route('student.exams.take', $this->exam));

        $response->assertOk();
        $state = $response->viewData('examState');
        $this->assertSame(3600, $state['remainingSeconds']);
        $this->assertFalse($state['started']);
        // route keys are encrypted with a random IV, so compare the stable part of the URL only
        $this->assertStringContainsString('/student/exams/', $state['draftUrl']);
        $this->assertStringEndsWith('/draft', $state['draftUrl']);
        $this->assertDatabaseHas('exam_attempts', ['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'started_at' => null]);
        // the page no longer depends on Alpine for the answers: radios carry data-qid for the engine
        $response->assertSee('data-qid="'.$this->q(0)->id.'"', false);
    }

    public function test_a_student_outside_the_exams_groups_cannot_autosave(): void
    {
        $stranger = $this->makeStudent();

        $this->actingAs($stranger, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => []])
            ->assertForbidden();
    }

    public function test_draft_is_saved_and_returned(): void
    {
        $t = $this->nowMs();

        $this->draft([$this->q(0)->id => $this->entry($this->correct(0), $t)])
            ->assertOk()
            ->assertJsonPath('saved', true)
            ->assertJsonPath('answers.'.$this->q(0)->id.'.v', $this->correct(0));

        $this->assertSame($this->correct(0), $this->storedAnswers()[$this->q(0)->id]['v']);
    }

    public function test_a_late_arriving_older_request_never_overwrites_a_newer_choice(): void
    {
        $t = $this->nowMs();
        $qid = $this->q(0)->id;

        // the student picks the right answer, then changes it to the wrong one while offline...
        $this->draft([$qid => $this->entry($this->wrong(0), $t + 5000)])->assertOk();
        // ...and the delayed request that carried the earlier (right) choice finally arrives
        $this->draft([$qid => $this->entry($this->correct(0), $t)])->assertOk()
            ->assertJsonPath('answers.'.$qid.'.v', $this->wrong(0));

        $this->assertSame($this->wrong(0), $this->storedAnswers()[$qid]['v']);

        // a genuinely newer choice still wins
        $this->draft([$qid => $this->entry($this->correct(0), $t + 9000)])->assertOk();
        $this->assertSame($this->correct(0), $this->storedAnswers()[$qid]['v']);
    }

    public function test_questions_are_merged_independently_so_nothing_is_lost(): void
    {
        $t = $this->nowMs();

        $this->draft([$this->q(0)->id => $this->entry($this->correct(0), $t)]);
        $this->draft([$this->q(1)->id => $this->entry($this->wrong(1), $t + 10)]);

        $stored = $this->storedAnswers();
        $this->assertSame($this->correct(0), $stored[$this->q(0)->id]['v']);
        $this->assertSame($this->wrong(1), $stored[$this->q(1)->id]['v']);
    }

    public function test_invalid_options_foreign_questions_and_clock_skew_are_sanitised(): void
    {
        $t = $this->nowMs();
        $otherExamOption = $this->makeExam($this->exam->subject, [$this->groupB], [], 1)->questions[0];

        $this->draft([
            $this->q(0)->id => $this->entry($otherExamOption->options[0]->id, $t),   // option of another question
            $this->q(1)->id => $this->entry($this->correct(1), $t + 10 * 24 * 3600 * 1000), // clock a week ahead
            $otherExamOption->id => $this->entry($otherExamOption->options[0]->id, $t),  // question of another exam
            999999999 => $this->entry(1, $t),
        ])->assertOk();

        $stored = $this->storedAnswers();
        $this->assertNull($stored[$this->q(0)->id]['v'], 'an option that does not belong to the question is dropped');
        $this->assertLessThanOrEqual($this->nowMs() + 300000 + 1000, $stored[$this->q(1)->id]['t'], 'timestamps are clamped to a sane skew');
        $this->assertArrayNotHasKey($otherExamOption->id, $stored);
        $this->assertArrayNotHasKey(999999999, $stored);
    }

    public function test_the_countdown_is_owned_by_the_server_and_survives_a_reload(): void
    {
        Carbon::setTestNow('2026-10-09 12:00:00');

        $this->draft([], ['begin' => true])->assertOk()->assertJsonPath('remainingSeconds', 3600)->assertJsonPath('started', true);

        Carbon::setTestNow('2026-10-09 12:10:00');
        $this->draft([])->assertOk()->assertJsonPath('remainingSeconds', 3000);

        // reload (or reconnect on another tab): the clock keeps its value and the draft comes back
        $this->draft([$this->q(0)->id => $this->entry($this->correct(0), $this->nowMs())]);
        Carbon::setTestNow('2026-10-09 12:20:00');
        $state = $this->actingAs($this->student, 'student')->get(route('student.exams.take', $this->exam))->viewData('examState');

        $this->assertTrue($state['started']);
        $this->assertSame(2400, $state['remainingSeconds']);
        $this->assertSame($this->correct(0), ((array) $state['answers'])[$this->q(0)->id]['v']);

        // begin is idempotent: pressing "start" again must not move the start time
        $this->draft([], ['begin' => true])->assertJsonPath('remainingSeconds', 2400);
    }

    public function test_a_start_registered_late_after_an_offline_period_gets_no_free_time(): void
    {
        Carbon::setTestNow('2026-10-09 12:00:00');

        // the student pressed "start" 20s ago while offline; the request only reaches the server now
        $this->draft([], ['begin' => true, 'begin_elapsed_ms' => 20000])->assertOk()->assertJsonPath('remainingSeconds', 3580);

        // a start can never be back-dated by more than the exam length, nor into the future
        $other = $this->makeStudent();
        $this->enroll($other, $this->groupA);
        $this->actingAs($other, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => [], 'begin' => true, 'begin_elapsed_ms' => 86400000])
            ->assertOk()->assertJsonPath('remainingSeconds', 0);
        $this->actingAs($other, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => [], 'begin' => true, 'begin_elapsed_ms' => -5])
            ->assertStatus(422);
    }

    public function test_time_never_goes_below_zero(): void
    {
        Carbon::setTestNow('2026-10-09 12:00:00');
        $this->draft([], ['begin' => true]);

        Carbon::setTestNow('2026-10-09 15:00:00');
        $this->draft([])->assertJsonPath('remainingSeconds', 0);
    }

    public function test_submit_after_a_reload_grades_the_saved_draft_even_if_the_form_arrives_empty(): void
    {
        $t = $this->nowMs();
        $this->draft([
            $this->q(0)->id => $this->entry($this->correct(0), $t),
            $this->q(1)->id => $this->entry($this->correct(1), $t),
            $this->q(2)->id => $this->entry('شرح الطالب', $t),
        ]);

        // the page was reloaded / re-opened after reconnecting and the browser lost the radios
        $response = $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), []);

        $grade = Grade::where(['exam_id' => $this->exam->id, 'student_id' => $this->student->id])->firstOrFail();
        $response->assertRedirectContains('/student/results/');
        $this->assertEquals(2, $grade->score, 'both multiple-choice answers survive and are graded');
        $this->assertSame(4, (int) $grade->max_score);
        $this->assertDatabaseHas('exam_answers', [
            'grade_id' => $grade->id, 'question_id' => $this->q(2)->id, 'essay_answer' => 'شرح الطالب',
        ]);
        $this->assertNotNull(ExamAttempt::where('exam_id', $this->exam->id)->first()->submitted_at);
    }

    public function test_submitted_client_state_with_newer_timestamps_beats_the_stored_draft(): void
    {
        $t = $this->nowMs();
        $qid = $this->q(0)->id;
        $this->draft([$qid => $this->entry($this->wrong(0), $t)]);

        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), [
            'answers_state' => json_encode([$qid => $this->entry($this->correct(0), $t + 5000)]),
        ]);

        $answer = Grade::where('exam_id', $this->exam->id)->firstOrFail()->answers->firstWhere('question_id', $qid);
        $this->assertSame($this->correct(0), $answer->selected_option_id);
        $this->assertTrue($answer->is_correct);
    }

    public function test_a_stale_submit_cannot_overwrite_a_newer_server_draft(): void
    {
        $t = $this->nowMs();
        $qid = $this->q(0)->id;
        $this->draft([$qid => $this->entry($this->correct(0), $t + 5000)]);

        // an old tab / delayed request submits its outdated copy of the answers
        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), [
            'answers' => [$qid => $this->wrong(0)],
            'answers_state' => json_encode([$qid => $this->entry($this->wrong(0), $t)]),
        ]);

        $answer = Grade::where('exam_id', $this->exam->id)->firstOrFail()->answers->firstWhere('question_id', $qid);
        $this->assertSame($this->correct(0), $answer->selected_option_id);
    }

    public function test_a_plain_form_post_without_timestamps_still_works_and_beats_the_draft(): void
    {
        $qid = $this->q(0)->id;
        $this->draft([$qid => $this->entry($this->wrong(0), $this->nowMs() - 60000)]);

        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), [
            'answers' => [$qid => $this->correct(0)],
        ]);

        $answer = Grade::where('exam_id', $this->exam->id)->firstOrFail()->answers->firstWhere('question_id', $qid);
        $this->assertSame($this->correct(0), $answer->selected_option_id);
    }

    public function test_submit_via_the_autosaving_page_answers_with_json_and_keeps_the_flash_message(): void
    {
        $response = $this->actingAs($this->student, 'student')
            ->postJson(route('student.exams.submit', $this->exam), ['auto_submitted' => 0]);

        $grade = Grade::where('exam_id', $this->exam->id)->firstOrFail();
        $response->assertOk()->assertJson(['submitted' => true]);
        $this->assertStringContainsString('/student/results/', $response->json('redirect'));
        $response->assertSessionHas('success');
    }

    public function test_a_retried_submit_does_not_create_a_second_grade(): void
    {
        $this->actingAs($this->student, 'student');
        $first = $this->postJson(route('student.exams.submit', $this->exam), []);
        $second = $this->postJson(route('student.exams.submit', $this->exam), []);

        $first->assertOk();
        $second->assertOk()->assertJson(['submitted' => true]);
        $this->assertSame(1, Grade::where(['exam_id' => $this->exam->id, 'student_id' => $this->student->id])->count());
        $this->assertSame(1, ExamAnswer::where(['exam_id' => $this->exam->id, 'student_id' => $this->student->id, 'question_id' => $this->q(0)->id])->count());
    }

    public function test_autosave_after_the_exam_was_submitted_tells_the_page_to_leave(): void
    {
        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), []);

        $response = $this->draft([])->assertStatus(409)->assertJson(['submitted' => true]);
        $this->assertStringContainsString('/student/results/', $response->json('redirect'));
    }

    public function test_the_grade_belongs_to_the_students_own_group_of_a_multi_group_exam(): void
    {
        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), []);

        $this->assertSame($this->groupB->id, Grade::where('exam_id', $this->exam->id)->firstOrFail()->group_id);
        $this->assertSame($this->groupA->id, $this->exam->group_id, 'the exam primary group is group A, the grade still follows the student');
    }

    public function test_a_submission_long_after_time_ran_out_is_flagged_for_the_teacher(): void
    {
        Carbon::setTestNow('2026-10-09 12:00:00');
        $this->draft([], ['begin' => true]);

        Carbon::setTestNow('2026-10-09 14:00:00'); // an hour past the deadline: the connection was down
        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), []);

        $this->assertStringContainsString('انقطاع الاتصال', Grade::where('exam_id', $this->exam->id)->firstOrFail()->notes);
    }

    public function test_deleting_a_grade_allows_a_clean_retake(): void
    {
        $t = $this->nowMs();
        $this->draft([$this->q(0)->id => $this->entry($this->correct(0), $t)], ['begin' => true]);
        $this->actingAs($this->student, 'student')->post(route('student.exams.submit', $this->exam), []);
        Grade::where('exam_id', $this->exam->id)->delete();

        $state = $this->actingAs($this->student, 'student')->get(route('student.exams.take', $this->exam))->viewData('examState');

        $this->assertFalse($state['started']);
        $this->assertSame(3600, $state['remainingSeconds']);
        $this->assertSame([], (array) $state['answers']);
    }

    public function test_a_violation_report_is_counted_once_even_if_it_is_sent_twice(): void
    {
        $this->actingAs($this->student, 'student');

        $first = $this->postJson(route('student.exams.violation', $this->exam), ['type' => 'tab_switch', 'id' => 'abc-1'])->assertOk();
        $again = $this->postJson(route('student.exams.violation', $this->exam), ['type' => 'tab_switch', 'id' => 'abc-1'])->assertOk();
        $other = $this->postJson(route('student.exams.violation', $this->exam), ['type' => 'tab_switch', 'id' => 'abc-2'])->assertOk();

        $this->assertSame(1, $first->json('total'));
        $this->assertSame(1, $again->json('total'), 'the replayed report did not add a second violation');
        $this->assertSame(2, $other->json('total'));
    }

    public function test_violations_made_while_offline_travel_with_the_submission_and_are_not_double_counted(): void
    {
        $this->actingAs($this->student, 'student');
        // one of the three already reached the server before the connection dropped
        $this->postJson(route('student.exams.violation', $this->exam), ['type' => 'tab_switch', 'id' => 'v1'])->assertOk();

        $this->postJson(route('student.exams.submit', $this->exam), [
            'auto_submitted' => 1,
            'pending_violations' => json_encode([
                ['type' => 'tab_switch', 'id' => 'v1'],          // already counted: ignored
                ['type' => 'tab_switch', 'id' => 'v2'],
                ['type' => 'fullscreen_exit', 'id' => 'v3'],
                ['type' => 'tab_switch'],                        // no id: malformed, ignored
            ]),
        ])->assertOk();

        $grade = Grade::where('exam_id', $this->exam->id)->firstOrFail();
        $this->assertSame(2, (int) $grade->tab_switch_count);
        $this->assertSame(1, (int) $grade->fullscreen_exit_count);
        $this->assertTrue($grade->auto_submitted);
    }

    public function test_autosave_is_rate_limited_per_student_not_per_ip(): void
    {
        $classmate = $this->makeStudent();
        $this->enroll($classmate, $this->groupA);

        // everybody in a classroom shares one public IP: one chatty tab must not lock the others out
        for ($i = 0; $i < 120; $i++) {
            $this->actingAs($this->student, 'student')
                ->postJson(route('student.exams.draft', $this->exam), ['answers' => []])->assertOk();
        }
        $this->actingAs($this->student, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => []])->assertStatus(429);

        $this->actingAs($classmate, 'student')
            ->postJson(route('student.exams.draft', $this->exam), ['answers' => []])->assertOk();
    }

    public function test_exam_without_a_duration_has_no_countdown(): void
    {
        $open = $this->makeExam($this->exam->subject, [$this->groupB], ['duration_minutes' => null], 1);

        $state = $this->actingAs($this->student, 'student')->get(route('student.exams.take', $open))->viewData('examState');
        $this->assertNull($state['remainingSeconds']);

        $this->actingAs($this->student, 'student')->postJson(route('student.exams.draft', $open), ['answers' => [], 'begin' => true])
            ->assertOk()->assertJsonPath('remainingSeconds', null);
    }
}
