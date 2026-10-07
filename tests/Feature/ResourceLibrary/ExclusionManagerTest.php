<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\StudentContentExclusion;
use App\Services\ResourceLibrary\ExclusionManager;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryJournal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Excluding a student from a video / lesson / unit: one switch per student, the same call for
 * admin and teacher, the teacher fenced into their own students, and always undoable.
 */
class ExclusionManagerTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function exclusions(): ExclusionManager
    {
        return app(ExclusionManager::class);
    }

    public function test_teacher_excludes_one_of_their_students_from_a_video(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);
        $student = $s['students']['A'];

        $result = $this->exclusions()->set($s['actorTeacher'], $video, $student->id, true);

        $this->assertSame([], $this->visibleIds($s, $student));
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertNotNull($result->undoToken);

        $row = StudentContentExclusion::first();
        $this->assertSame('teacher', $row->excluded_by_type, 'who excluded the student is recorded');
        $this->assertSame($s['teacher']->id, (int) $row->excluded_by_id);
    }

    public function test_a_teacher_cannot_exclude_a_student_of_a_colleagues_group(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        $this->expectException(LibraryException::class);
        $this->exclusions()->set($s['actorTeacher'], $video, $s['students']['C']->id, true);
    }

    public function test_admin_can_exclude_any_enrolled_student_and_a_stranger_is_refused(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        $this->exclusions()->set($s['actorAdmin'], $video, $s['students']['C']->id, true);
        $this->assertSame([], $this->visibleIds($s, $s['students']['C']));

        $stranger = \App\Models\Student::factory()->create();
        $this->expectException(LibraryException::class);
        $this->exclusions()->set($s['actorAdmin'], $video, $stranger->id, true);
    }

    public function test_excluding_from_a_unit_cascades_and_from_a_lesson_does_not_touch_the_sibling(): void
    {
        $s = $this->scenario();
        $one = $this->makeResource($s, $s['lesson1']);
        $two = $this->makeResource($s, $s['lesson2']);

        $this->exclusions()->set($s['actorTeacher'], $s['lesson1'], $s['students']['A']->id, true);
        $this->assertSame([$two->id], $this->visibleIds($s, $s['students']['A']));

        $this->exclusions()->set($s['actorTeacher'], $s['unit'], $s['students']['B']->id, true);
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']), 'the whole unit disappears for B');
        $this->assertSame([$one->id, $two->id], $this->visibleIds($s, $s['students']['C']));
    }

    public function test_the_toggle_is_idempotent_and_undo_reverts_it(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);
        $student = $s['students']['A'];

        $first = $this->exclusions()->set($s['actorTeacher'], $video, $student->id, true);
        $again = $this->exclusions()->set($s['actorTeacher'], $video, $student->id, true);

        $this->assertSame(1, StudentContentExclusion::count());
        $this->assertNull($again->undoToken, 'nothing changed, nothing to undo');

        app(LibraryJournal::class)->undo($s['actorTeacher'], $first->undoToken);

        $this->assertSame(0, StudentContentExclusion::count());
        $this->assertSame([$video->id], $this->visibleIds($s, $student));
    }

    public function test_the_students_table_lists_the_audience_with_the_state_of_each_toggle(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        $this->exclusions()->set($s['actorTeacher'], $video, $s['students']['A']->id, true);

        $rows = $this->exclusions()->students($s['actorTeacher'], $video);

        $this->assertCount(2, $rows, 'only the audience groups of this teacher');
        $this->assertTrue($rows->firstWhere('student_id', $s['students']['A']->id)['excluded']);
        $this->assertFalse($rows->firstWhere('student_id', $s['students']['B']->id)['excluded']);
        $this->assertNull($rows->firstWhere('student_id', $s['students']['C']->id), 'a colleague\'s student never shows up');

        $adminRows = $this->exclusions()->students($s['actorAdmin'], $this->makeResource($s, $s['lesson1']));
        $this->assertCount(3, $adminRows, 'shared content: every enrolled student, for the admin');
    }

    public function test_the_table_marks_students_excluded_through_the_unit(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);
        $this->exclusions()->set($s['actorAdmin'], $s['unit'], $s['students']['A']->id, true);

        $row = $this->exclusions()->students($s['actorAdmin'], $video)->firstWhere('student_id', $s['students']['A']->id);

        $this->assertFalse($row['excluded'], 'not excluded from the video itself');
        $this->assertSame('الوحدة', $row['inherited'], 'but the table says why the student does not see it');
    }

    public function test_replace_keeps_exclusions_outside_the_teachers_reach(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);
        $this->exclusions()->set($s['actorAdmin'], $video, $s['students']['C']->id, true);
        $this->exclusions()->set($s['actorAdmin'], $video, $s['students']['A']->id, true);

        // the teacher saves a list that no longer contains A and knows nothing about C
        $this->exclusions()->replace($s['actorTeacher'], $video, [$s['students']['B']->id]);

        $excluded = StudentContentExclusion::pluck('student_id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $this->assertSame([$s['students']['B']->id, $s['students']['C']->id], $excluded, 'C (admin\'s) kept, A released, B added');
    }
}
