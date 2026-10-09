<?php

namespace Tests\Concerns;

use App\Models\Exam;
use App\Models\Group;
use App\Models\Question;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;

/** Small builders shared by the multi-group exam / autosave / PDF tests. */
trait BuildsExamScenario
{
    protected function makeStudent(array $overrides = []): Student
    {
        return Student::factory()->create(array_merge(['email_verified_at' => now()], $overrides));
    }

    protected function enroll(Student $student, Group $group, string $status = 'fully_paid'): Registration
    {
        return Registration::factory()->create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'subject_id' => $group->subject_id,
            'status' => $status,
        ]);
    }

    /** @return array{0: Subject, 1: Teacher, 2: Group, 3: Group} */
    protected function makeSubjectWithTwoGroups(?Teacher $teacher = null): array
    {
        $subject = Subject::factory()->create();
        $teacher ??= Teacher::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'name' => 'مجموعة أ']);
        $groupB = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'name' => 'مجموعة ب']);

        return [$subject, $teacher, $groupA, $groupB];
    }

    /**
     * A published exam over the given groups with one true/false-style multiple choice
     * question per entry of $questions (['points' => n] optional) and an essay if asked.
     *
     * @param  array<int, Group>  $groups
     */
    protected function makeExam(Subject $subject, array $groups, array $overrides = [], int $choiceQuestions = 2, bool $withEssay = false): Exam
    {
        $exam = Exam::create(array_merge([
            'subject_id' => $subject->id,
            'group_id' => $groups[0]->id,
            'title' => 'امتحان تجريبي',
            'start_time' => now()->subHour(),
            'end_time' => now()->addDay(),
            'duration_minutes' => 60,
            'status' => 'published',
            'audience' => 'students',
        ], $overrides));
        $exam->groups()->sync(collect($groups)->pluck('id')->all());

        for ($i = 1; $i <= $choiceQuestions; $i++) {
            $q = Question::create(['exam_id' => $exam->id, 'type' => 'multiple_choice', 'content' => "سؤال $i", 'points' => 1, 'sort_order' => $i]);
            $q->options()->create(['option_text' => 'صح', 'is_correct' => true]);
            $q->options()->create(['option_text' => 'خطأ', 'is_correct' => false]);
        }

        if ($withEssay) {
            Question::create(['exam_id' => $exam->id, 'type' => 'essay', 'content' => 'اشرح', 'points' => 2, 'sort_order' => 99]);
        }

        return $exam->fresh(['questions.options']);
    }
}
