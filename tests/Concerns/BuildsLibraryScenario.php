<?php

namespace Tests\Concerns;

use App\Models\Admin;
use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Models\Teacher;
use App\Services\ResourceLibrary\LibraryActor;

/**
 * One subject, three groups, two teachers, an admin, one student per group:
 *
 *   teacher     teaches group A and group B
 *   colleague   teaches group C of the same subject
 *   admin       reaches everything
 *
 *   stage > unit > lesson1, lesson2        (all active, declared shared)
 */
trait BuildsLibraryScenario
{
    /** @return array<string, mixed> */
    protected function scenario(): array
    {
        $subject = Subject::factory()->create();

        $teacher = Teacher::factory()->create();
        $colleague = Teacher::factory()->create();
        $teacher->subjects()->attach($subject->id);
        $colleague->subjects()->attach($subject->id);

        $groupA = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'name' => 'A']);
        $groupB = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $teacher->id, 'name' => 'B']);
        $groupC = Group::factory()->create(['subject_id' => $subject->id, 'teacher_id' => $colleague->id, 'name' => 'C']);

        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);
        $unit = EducationalUnit::factory()->create(['educational_stage_id' => $stage->id, 'is_shared' => true, 'sort_order' => 1]);
        $lesson1 = EducationalLesson::factory()->create(['educational_unit_id' => $unit->id, 'is_shared' => true, 'sort_order' => 1]);
        $lesson2 = EducationalLesson::factory()->create(['educational_unit_id' => $unit->id, 'is_shared' => true, 'sort_order' => 2]);

        $students = [];
        foreach (['A' => $groupA, 'B' => $groupB, 'C' => $groupC] as $name => $group) {
            $students[$name] = Student::factory()->create();
            Registration::factory()->create([
                'student_id' => $students[$name]->id,
                'subject_id' => $subject->id,
                'group_id' => $group->id,
                'status' => 'fully_paid',
            ]);
        }

        $admin = Admin::create([
            'name' => 'Library Admin',
            'email' => 'library-admin-'.uniqid().'@example.test',
            'password' => 'secret-pass-123',
            'status' => 1,
        ]);

        return [
            'subject' => $subject,
            'teacher' => $teacher,
            'colleague' => $colleague,
            'admin' => $admin,
            'actorTeacher' => LibraryActor::teacher($teacher),
            'actorColleague' => LibraryActor::teacher($colleague),
            'actorAdmin' => LibraryActor::admin($admin),
            'groupA' => $groupA,
            'groupB' => $groupB,
            'groupC' => $groupC,
            'stage' => $stage,
            'unit' => $unit,
            'lesson1' => $lesson1,
            'lesson2' => $lesson2,
            'students' => $students,
        ];
    }

    /** A live, active, placed resource linked to the given groups (or shared when none are given). */
    protected function makeResource(array $s, ?EducationalLesson $lesson, array $groups = [], array $attributes = []): SubjectResource
    {
        $factory = SubjectResource::factory();
        if ($lesson) {
            $factory = $factory->forLesson($lesson);
        }

        $resource = $factory->create(array_merge(
            ['subject_id' => $s['subject']->id, 'is_shared' => $groups === []],
            $attributes,
        ));

        foreach ($groups as $group) {
            $resource->groups()->attach($group->id, ['is_active' => true]);
        }

        return $resource->fresh();
    }

    /**
     * Exclude students from a resource / lesson / unit, bypassing the manager (test setup).
     *
     * @param  array<int>  $studentIds
     */
    protected function exclude(\Illuminate\Database\Eloquent\Model $target, int $subjectId, array $studentIds): void
    {
        foreach ($studentIds as $studentId) {
            \App\Models\StudentContentExclusion::create([
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'excludable_type' => $target->getMorphClass(),
                'excludable_id' => $target->getKey(),
            ]);
        }
    }

    /** Ids a student sees in the subject through the gate. */
    protected function visibleIds(array $s, Student $student): array
    {
        return app(\App\Services\StudentContentGate::class)
            ->visibleResources($student->id, $s['subject']->id)
            ->pluck('subject_resources.id')
            ->map(fn ($id) => (int) $id)
            ->sort()->values()->all();
    }
}
