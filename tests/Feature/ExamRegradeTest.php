<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Models\Student;
use App\Services\ExamRegrader;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsExamScenario;
use Tests\TestCase;

/**
 * Re-marking submitted exams against the current answer key: a student who answered but got 0
 * (wrong / missing key at submit time, lost option on a row) gets the right mark back,
 * and nothing is ever deleted or guessed.
 */
class ExamRegradeTest extends TestCase
{
    use BuildsExamScenario, DatabaseTransactions;

    private Student $student;

    private Exam $exam;

    protected function setUp(): void
    {
        parent::setUp();

        [$subject, , $groupA] = $this->makeSubjectWithTwoGroups();
        $this->student = $this->makeStudent();
        $this->enroll($this->student, $groupA);
        $this->exam = $this->makeExam($subject, [$groupA], [], 2, true);
    }

    private function correct(int $n): int
    {
        return $this->exam->questions[$n]->options->firstWhere('is_correct', true)->id;
    }

    private function wrong(int $n): int
    {
        return $this->exam->questions[$n]->options->firstWhere('is_correct', false)->id;
    }

    private function submit(array $answers): Grade
    {
        $this->actingAs($this->student, 'student')
            ->post(route('student.exams.submit', $this->exam), ['answers' => $answers])
            ->assertRedirect();

        return Grade::where('exam_id', $this->exam->id)->where('student_id', $this->student->id)->firstOrFail();
    }

    /** Simulates an exam published with no correct option marked: everybody is marked 0. */
    private function clearKey(): void
    {
        foreach ($this->exam->questions as $q) {
            $q->options()->update(['is_correct' => false]);
        }
    }

    private function restoreKey(): void
    {
        foreach ($this->exam->questions->where('type', '!=', 'essay') as $q) {
            $q->options()->where('option_text', 'صح')->update(['is_correct' => true]);
        }
        $this->exam->unsetRelation('questions');
    }

    public function test_a_student_marked_zero_because_of_a_missing_key_gets_the_right_mark_after_regrading(): void
    {
        $this->clearKey();
        $grade = $this->submit([
            $this->exam->questions[0]->id => $this->correct(0),
            $this->exam->questions[1]->id => $this->correct(1),
        ]);
        $this->assertEquals(0, $grade->score);

        $this->restoreKey();
        $report = app(ExamRegrader::class)->regradeExam($this->exam);

        $grade->refresh();
        $this->assertEquals(2, $grade->score);
        $this->assertEquals(4, $grade->max_score);
        $this->assertSame(1, $report['changed']);
        $this->assertStringContainsString(ExamRegrader::NOTE, $grade->notes);
        $this->assertSame(2, ExamAnswer::where('grade_id', $grade->id)->where('is_correct', true)->count());
        $this->assertSame(3, ExamAnswer::where('grade_id', $grade->id)->count(), 'no answer row is added or removed');
    }

    public function test_dry_run_changes_nothing(): void
    {
        $this->clearKey();
        $grade = $this->submit([$this->exam->questions[0]->id => $this->correct(0)]);
        $this->restoreKey();

        $report = app(ExamRegrader::class)->regradeExam($this->exam, true);

        $this->assertSame(1, $report['changed']);
        $this->assertEquals(1, $report['details'][0]['new']);
        $this->assertEquals(0, $grade->fresh()->score);
    }

    public function test_essay_marks_given_by_the_teacher_are_kept(): void
    {
        $this->clearKey();
        $essay = $this->exam->questions->firstWhere('type', 'essay');
        $grade = $this->submit([$this->exam->questions[0]->id => $this->correct(0), $essay->id => 'إجابة']);
        ExamAnswer::where('grade_id', $grade->id)->where('question_id', $essay->id)->update(['points_earned' => 1.5]);
        $grade->update(['score' => 1.5]);

        $this->restoreKey();
        app(ExamRegrader::class)->regradeExam($this->exam);

        $this->assertEquals(2.5, $grade->fresh()->score);
        $this->assertEquals(1.5, ExamAnswer::where('grade_id', $grade->id)->where('question_id', $essay->id)->value('points_earned'));
    }

    public function test_a_lost_choice_is_recovered_from_the_autosaved_draft(): void
    {
        $q0 = $this->exam->questions[0];
        $grade = $this->submit([$q0->id => $this->correct(0)]);
        // the row lost its option (and its mark), but the autosaved draft still has the choice
        ExamAnswer::where('grade_id', $grade->id)->where('question_id', $q0->id)
            ->update(['selected_option_id' => null, 'is_correct' => false, 'points_earned' => 0]);
        $grade->update(['score' => 0]);
        ExamAttempt::where('exam_id', $this->exam->id)->where('student_id', $this->student->id)
            ->update(['answers' => json_encode([$q0->id => ['v' => $this->correct(0), 't' => 1]])]);

        $report = app(ExamRegrader::class)->regradeExam($this->exam);

        $this->assertEquals(1, $grade->fresh()->score);
        $this->assertSame(1, $report['details'][0]['recovered']);
        $this->assertSame($this->correct(0), ExamAnswer::where('grade_id', $grade->id)->where('question_id', $q0->id)->value('selected_option_id'));
    }

    public function test_an_unknown_choice_keeps_its_points_and_a_grade_without_answers_is_untouched(): void
    {
        $q0 = $this->exam->questions[0];
        $grade = $this->submit([$q0->id => $this->correct(0)]);
        // the chosen option is gone (no draft either): the mark it earned is not taken away
        ExamAnswer::where('grade_id', $grade->id)->where('question_id', $q0->id)->update(['selected_option_id' => null]);

        app(ExamRegrader::class)->regradeExam($this->exam);
        $this->assertEquals(1, $grade->fresh()->score);

        // a legacy grade with no answer rows and no draft can not be re-marked: left exactly as is
        $other = $this->makeStudent();
        $legacy = Grade::create(['student_id' => $other->id, 'group_id' => $this->exam->group_id, 'exam_id' => $this->exam->id, 'exam_name' => 'x', 'score' => 3, 'max_score' => 4]);
        $report = app(ExamRegrader::class)->regradeExam($this->exam);

        $this->assertSame(1, $report['skipped']);
        $this->assertEquals(3, $legacy->fresh()->score);
    }

    public function test_a_question_added_after_submitting_does_not_change_the_students_mark(): void
    {
        $grade = $this->submit([$this->exam->questions[0]->id => $this->correct(0)]);
        $before = [(float) $grade->score, (float) $grade->max_score];

        $extra = $this->exam->questions()->create(['type' => 'multiple_choice', 'content' => 'جديد', 'points' => 5, 'sort_order' => 50]);
        $extra->options()->create(['option_text' => 'صح', 'is_correct' => true]);
        $this->exam->unsetRelation('questions');

        app(ExamRegrader::class)->regradeExam($this->exam);

        $grade->refresh();
        $this->assertSame($before, [(float) $grade->score, (float) $grade->max_score]);
        $this->assertFalse(ExamAnswer::where('grade_id', $grade->id)->where('question_id', $extra->id)->exists());
    }

    public function test_admin_can_regrade_from_the_results_page(): void
    {
        $this->clearKey();
        $grade = $this->submit([$this->exam->questions[0]->id => $this->correct(0), $this->exam->questions[1]->id => $this->wrong(1)]);
        $this->restoreKey();

        $this->actingAs($this->adminUser(), 'admin')
            ->post(route('exams.regrade', $this->exam))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals(1, $grade->fresh()->score);
    }

    public function test_the_command_previews_by_default_and_saves_with_apply(): void
    {
        $this->clearKey();
        $grade = $this->submit([$this->exam->questions[0]->id => $this->correct(0)]);
        $this->restoreKey();

        $this->artisan('grades:regrade', ['exam' => $this->exam->id])->assertSuccessful();
        $this->assertEquals(0, $grade->fresh()->score);

        $this->artisan('grades:regrade', ['exam' => $this->exam->id, '--apply' => true])->assertSuccessful();
        $this->assertEquals(1, $grade->fresh()->score);
    }

    public function test_a_choice_question_cannot_be_saved_without_a_correct_answer(): void
    {
        $this->actingAs($this->adminUser(), 'admin')
            ->put(route('exams.update', $this->exam), [
                'subject_id' => $this->exam->subject_id,
                'group_ids' => [$this->exam->group_id],
                'title' => $this->exam->title,
                'status' => 'published',
                'audience' => 'students',
                'questions' => [[
                    'type' => 'multiple_choice',
                    'content' => 'سؤال',
                    'points' => 1,
                    'options' => [
                        ['option_text' => 'أ', 'is_correct' => 0],
                        ['option_text' => 'ب', 'is_correct' => 0],
                    ],
                ]],
            ])
            ->assertSessionHasErrors('questions.0.options');
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
}
