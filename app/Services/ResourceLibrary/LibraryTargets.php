<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\RouteKey;
use Illuminate\Database\Eloquent\Model;

/**
 * Turns the encrypted keys the browser sends back into models - and checks the actor may touch them.
 *
 * Route keys are encrypted with a random IV, so the same row has a different key every time it
 * is rendered; this class is the only place that decodes them.
 */
final class LibraryTargets
{
    /** URL segment => model */
    public const TYPES = [
        'resources' => SubjectResource::class,
        'units' => EducationalUnit::class,
        'lessons' => EducationalLesson::class,
    ];

    /**
     * @return SubjectResource|EducationalUnit|EducationalLesson
     */
    public function resolve(LibraryActor $actor, string $type, string $key, bool $withTrashed = false): Model
    {
        $class = self::TYPES[$type] ?? throw LibraryException::notFound();
        $id = RouteKey::decrypt($key) ?? throw LibraryException::notFound();

        $query = $class::query();
        if ($withTrashed) {
            $query->withTrashed();
        }

        /** @var Model|null $model */
        $model = $query->find($id);
        if (! $model) {
            throw LibraryException::notFound();
        }

        $subjectId = match (true) {
            $model instanceof SubjectResource => (int) $model->subject_id,
            $model instanceof EducationalUnit => (int) $model->stage?->subject_id,
            default => (int) $model->unit()->withTrashed()->first()?->stage?->subject_id,
        };

        if (! $subjectId || ! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::notFound();
        }

        // an invisible resource must look like a missing one
        if ($model instanceof SubjectResource && ! $actor->canSeeResource($model)) {
            throw LibraryException::notFound();
        }

        return $model;
    }

    public function subject(LibraryActor $actor, string $key): Subject
    {
        $id = RouteKey::decrypt($key) ?? throw LibraryException::notFound();
        $subject = Subject::find($id) ?? throw LibraryException::notFound();

        if (! $actor->canAccessSubject($subject->id)) {
            throw LibraryException::forbidden();
        }

        return $subject;
    }

    /**
     * Placement targets sent by the browser: [{type: lesson|unit|general, key: ...}, ...]
     *
     * @param  array<int, array{type?: string, key?: string}>  $targets
     * @return array<int, EducationalLesson|EducationalUnit|string>
     */
    public function placements(LibraryActor $actor, array $targets): array
    {
        $resolved = [];

        foreach ($targets as $target) {
            $type = $target['type'] ?? '';

            if ($type === 'general') {
                $resolved[] = PlacementManager::GENERAL;

                continue;
            }

            $plural = match ($type) {
                'lesson' => 'lessons',
                'unit' => 'units',
                default => throw LibraryException::invalid('مكان غير معروف.', 'bad_target'),
            };

            $resolved[] = $this->resolve($actor, $plural, (string) ($target['key'] ?? ''));
        }

        return $resolved;
    }

    public function group(string|int $idOrKey): Group
    {
        return Group::findOrFail((int) $idOrKey);
    }

    public function student(string|int $id): Student
    {
        return Student::findOrFail((int) $id);
    }

    /** Subject of any library target. */
    public function subjectIdOf(Model $target): int
    {
        return match (true) {
            $target instanceof SubjectResource => (int) $target->subject_id,
            $target instanceof EducationalUnit => (int) $target->stage?->subject_id,
            default => (int) $target->unit()->withTrashed()->first()?->stage?->subject_id,
        };
    }
}
