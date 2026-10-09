<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\EncryptsRouteKey;

class Exam extends Model
{
    use HasFactory, EncryptsRouteKey;

    /**
     * Registration statuses that may see / take an exam, and therefore the ones
     * that get its live notifications. Kept in one place so "who can take it"
     * and "who is told about it" can never drift apart.
     */
    public const ACCESS_REGISTRATION_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    protected $fillable = [
        'subject_id',
        'group_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'excluded_student_ids',
        'start_alert_sent_at',
        'audience',
        'allow_student_review',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'excluded_student_ids' => 'array',
        'start_alert_sent_at' => 'datetime',
        'allow_student_review' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /** Primary (first selected) group. The full target list is groups(). */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /** Every group this exam is published to (one exam, many groups). */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'exam_group')->withTimestamps();
    }

    /** @return array<int, int> target group ids (pivot + legacy primary group) */
    public function allGroupIds(): array
    {
        $ids = $this->relationLoaded('groups')
            ? $this->groups->pluck('id')
            : $this->groups()->pluck('groups.id');

        return $ids->push($this->group_id)
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /** Group names for display, e.g. "مجموعة أ، مجموعة ب". */
    public function groupNames(?array $onlyGroupIds = null): string
    {
        $groups = $this->relationLoaded('groups') && $this->groups->isNotEmpty()
            ? $this->groups
            : collect([$this->group])->filter();

        if ($onlyGroupIds !== null) {
            $groups = $groups->whereIn('id', $onlyGroupIds);
        }

        return $groups->pluck('name')->filter()->implode('، ') ?: '-';
    }

    /** The teacher teaches at least one of the groups this exam targets (view / grade it). */
    public function isTaughtBy(int $teacherId): bool
    {
        return Group::query()
            ->whereIn('id', $this->allGroupIds())
            ->where('teacher_id', $teacherId)
            ->exists();
    }

    /** Every target group belongs to the teacher (safe to edit / reorder it). */
    public function isOwnedBy(int $teacherId): bool
    {
        $ids = $this->allGroupIds();

        return $ids !== []
            && Group::query()->whereIn('id', $ids)->where('teacher_id', $teacherId)->count() === count($ids);
    }

    /** Exams targeting at least one of the given groups. */
    public function scopeForGroups($query, iterable $groupIds)
    {
        $ids = collect($groupIds)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();

        return $query->where(function ($q) use ($ids) {
            $q->whereIn('exams.group_id', $ids)
                ->orWhereIn('exams.id', fn ($sub) => $sub->select('exam_id')->from('exam_group')->whereIn('group_id', $ids));
        });
    }

    /**
     * excluded_student_ids may hold ints or numeric strings depending on when
     * the exam was saved, so always compare as ints.
     */
    public function excludedStudentIds(): array
    {
        return collect($this->excluded_student_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function isStudentExcluded(int $studentId): bool
    {
        return in_array($studentId, $this->excludedStudentIds(), true);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function guestSubmissions()
    {
        return $this->hasMany(ExamGuestSubmission::class);
    }

    public function allowsGuests(): bool
    {
        return in_array($this->audience, ['guests', 'both'], true);
    }

    public function allowsStudents(): bool
    {
        return in_array($this->audience, ['students', 'both'], true);
    }

    public function studentGrants()
    {
        return $this->hasMany(StudentExamGrant::class);
    }
}
