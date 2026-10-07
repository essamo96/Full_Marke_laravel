<?php

namespace App\Services\ResourceLibrary;

use App\Models\Admin;
use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Registration;
use App\Models\SubjectResource;
use App\Models\Teacher;

/**
 * Who is acting on the library, and how far their reach goes.
 *
 * The library has ONE set of actions (see the managers in this namespace); an admin and a
 * teacher both call them. The only difference is the actor's reach:
 *
 *   admin    every subject, every group, every student
 *   teacher  the subjects they teach, and inside them only THEIR groups and the students
 *            registered in those groups
 *
 * so "a teacher can do everything an admin can, within the scope of their groups" is a
 * property of this class rather than of two copies of controller code.
 */
final class LibraryActor
{
    public const ADMIN = 'admin';

    public const TEACHER = 'teacher';

    /** @var array<int, array<int>> subject id => group ids the actor may manage */
    private array $groupCache = [];

    /** @var array<int, array<int>> subject id => every group id of the subject */
    private array $allGroupCache = [];

    private ?array $subjectIdCache = null;

    private function __construct(
        public readonly string $type,
        private readonly Admin|Teacher $user,
    ) {}

    public static function admin(Admin $admin): self
    {
        return new self(self::ADMIN, $admin);
    }

    public static function teacher(Teacher $teacher): self
    {
        return new self(self::TEACHER, $teacher);
    }

    public function id(): int
    {
        return (int) $this->user->getAuthIdentifier();
    }

    public function isAdmin(): bool
    {
        return $this->type === self::ADMIN;
    }

    public function isTeacher(): bool
    {
        return $this->type === self::TEACHER;
    }

    public function name(): string
    {
        return (string) ($this->user->name ?? ($this->isAdmin() ? 'مشرف' : 'معلم'));
    }

    public function is(?string $type, int|string|null $id): bool
    {
        return $type === $this->type && (int) $id === $this->id();
    }

    /** created_by / excluded_by / deleted_by style columns for this actor. */
    public function stamp(string $prefix = 'created_by'): array
    {
        return ["{$prefix}_type" => $this->type, "{$prefix}_id" => $this->id()];
    }

    // ------------------------------------------------------------------ reach

    /**
     * Subjects the actor may open. null means "all of them" (admin).
     *
     * A teacher reaches a subject through the subject_teacher pivot OR by owning a group of it.
     *
     * @return array<int>|null
     */
    public function subjectIds(): ?array
    {
        if (! $this->user instanceof Teacher) {
            return null;
        }

        return $this->subjectIdCache ??= $this->user->subjects()->pluck('subjects.id')
            ->merge(Group::where('teacher_id', $this->id())->pluck('subject_id'))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function canAccessSubject(int $subjectId): bool
    {
        $ids = $this->subjectIds();

        return $ids === null || in_array($subjectId, $ids, true);
    }

    /**
     * Groups of the subject the actor may manage.
     *
     * @return array<int>
     */
    public function groupIdsFor(int $subjectId): array
    {
        return $this->groupCache[$subjectId] ??= Group::query()
            ->where('subject_id', $subjectId)
            ->when($this->isTeacher(), fn ($q) => $q->where('teacher_id', $this->id()))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** All groups of a subject, whoever teaches them. */
    public function allGroupIdsOf(int $subjectId): array
    {
        return $this->allGroupCache[$subjectId] ??= Group::where('subject_id', $subjectId)
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function canManageGroup(int $subjectId, int $groupId): bool
    {
        return in_array($groupId, $this->groupIdsFor($subjectId), true);
    }

    /** True when the actor reaches every group of the subject (always for admins). */
    public function ownsAllGroupsOf(int $subjectId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $all = $this->allGroupIdsOf($subjectId);

        return $all !== [] && array_diff($all, $this->groupIdsFor($subjectId)) === [];
    }

    /**
     * Students the actor may exclude / list in a subject. null means "any registered student" (admin).
     *
     * @return array<int>|null
     */
    public function studentIdsFor(int $subjectId): ?array
    {
        if ($this->isAdmin()) {
            return null;
        }

        return Registration::query()
            ->where('subject_id', $subjectId)
            ->whereIn('group_id', $this->groupIdsFor($subjectId) ?: [0])
            ->pluck('student_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    // ------------------------------------------------------- resource ownership

    /**
     * Can the actor see this resource at all in the management screens?
     * Teachers see what reaches their groups, their own uploads, and drafts nobody owns yet.
     */
    public function canSeeResource(SubjectResource $resource): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->canAccessSubject((int) $resource->subject_id)) {
            return false;
        }

        if ($this->is($resource->created_by_type, $resource->created_by_id)) {
            return true;
        }

        $mine = $this->groupIdsFor((int) $resource->subject_id);
        $audience = $this->audienceOf($resource);

        if ($audience['shared']) {
            return true;
        }

        // A paused group still counts: the teacher must be able to see it to resume it.
        $linked = [...$audience['groups'], ...$audience['paused']];

        if (array_intersect($linked, $mine) !== []) {
            return true;
        }

        // A draft (nobody is allowed to see it yet) belongs to whoever created it; legacy
        // drafts with no recorded creator stay visible to the subject's teachers.
        return $linked === [] && $resource->created_by_id === null;
    }

    /**
     * Can the actor change the resource itself (title, file, placements, delete)?
     * Only when nothing outside the actor's own groups depends on it.
     */
    public function canManageResource(SubjectResource $resource): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->canSeeResource($resource)) {
            return false;
        }

        $subjectId = (int) $resource->subject_id;
        $audience = $this->audienceOf($resource);

        if ($audience['shared']) {
            return $this->ownsAllGroupsOf($subjectId);
        }

        $linked = [...$audience['groups'], ...$audience['paused']];

        if ($linked === []) {
            return true; // a draft that canSeeResource() already let through
        }

        return array_diff($linked, $this->groupIdsFor($subjectId)) === [];
    }

    public function canManageContainer(EducationalUnit|EducationalLesson $container, int $subjectId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (! $this->canAccessSubject($subjectId)) {
            return false;
        }

        if ($container->is_shared) {
            return $this->ownsAllGroupsOf($subjectId);
        }

        $declared = ($container->relationLoaded('groups') ? $container->groups : $container->groups()->get())
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        return array_diff($declared, $this->groupIdsFor($subjectId)) === [];
    }

    /**
     * @return array{shared: bool, groups: array<int>, paused: array<int>}
     *         groups = groups with an ACTIVE link, paused = groups with a paused link
     */
    public function audienceOf(SubjectResource $resource): array
    {
        $links = $resource->relationLoaded('groupLinks')
            ? $resource->groupLinks->whereNull('deleted_at')
            : $resource->groupLinks()->get();

        return [
            'shared' => (bool) $resource->is_shared,
            'groups' => $links->where('is_active', true)->pluck('group_id')->map(fn ($id) => (int) $id)->values()->all(),
            'paused' => $links->where('is_active', false)->pluck('group_id')->map(fn ($id) => (int) $id)->values()->all(),
        ];
    }
}
