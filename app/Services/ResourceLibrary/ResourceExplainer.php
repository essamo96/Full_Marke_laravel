<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\ResourceGroupLink;
use App\Models\ResourcePlacement;
use App\Models\Student;
use App\Models\StudentContentExclusion;
use App\Models\StudentContentGrant;
use App\Models\SubjectResource;
use App\Services\StudentContentGate;

/**
 * "Why can't this student see this video?" - the answer in plain Arabic, one line per rule.
 *
 * It re-evaluates every rule of StudentVisibility step by step (kill switch, enrolment,
 * exclusion, audience, placement) so support can see exactly which one blocks the student.
 * A test keeps it honest: its verdict must always equal StudentContentGate::canAccess().
 */
final class ResourceExplainer
{
    public function __construct(private readonly StudentContentGate $gate) {}

    /**
     * @return array{visible: bool, summary: string, steps: array<int, array{ok: bool, label: string, detail: ?string}>}
     */
    public function explain(LibraryActor $actor, SubjectResource $resource, Student $student): array
    {
        $subjectId = (int) $resource->subject_id;

        if (! $actor->canSeeResource($resource)) {
            throw LibraryException::forbidden();
        }

        $pool = $actor->studentIdsFor($subjectId);
        if ($pool !== null && ! in_array((int) $student->id, $pool, true)) {
            throw LibraryException::forbidden('يمكنك الاستعلام عن طلاب مجموعاتك فقط.');
        }

        $steps = [];
        $step = function (bool $ok, string $label, ?string $detail = null) use (&$steps) {
            $steps[] = ['ok' => $ok, 'label' => $label, 'detail' => $detail];

            return $ok;
        };

        // 1. not deleted
        $alive = $step(! $resource->trashed(), 'المورد غير محذوف', $resource->trashed() ? 'المورد في سلة المحذوفات' : null);

        // 2. kill switch
        $active = $step((bool) $resource->is_active, 'المورد مفعّل (مفتاح الإيقاف)',
            $resource->is_active ? null : 'تم إيقاف عرضه عن كل الطلاب'.($resource->deactivated_at ? ' بتاريخ '.$resource->deactivated_at->format('Y-m-d') : ''));

        // 3. enrolment and groups
        $enrolled = $this->gate->isEnrolled((int) $student->id, $subjectId);
        $groupIds = $this->gate->groupIds((int) $student->id, $subjectId);
        $groupNames = Group::whereIn('id', $groupIds)->pluck('name')->join('، ');
        $step($enrolled, 'الطالب مسجل في المادة', $enrolled ? ($groupNames ? "المجموعة: {$groupNames}" : 'بدون مجموعة') : 'لا يوجد تسجيل فعّال في هذه المادة');

        // 4. exclusion on the resource itself
        $excluded = StudentContentExclusion::query()
            ->where('student_id', $student->id)
            ->where('excludable_type', $resource->getMorphClass())
            ->where('excludable_id', $resource->id)
            ->exists();
        $notExcluded = $step(! $excluded, 'الطالب غير مستثنى من هذا المورد', $excluded ? 'تم استثناؤه من المورد مباشرة' : null);

        // 5. audience
        $links = ResourceGroupLink::where('subject_resource_id', $resource->id)->get();
        $pausedAll = $groupIds !== [] && collect($groupIds)->every(fn ($g) => $links->where('group_id', $g)->where('is_active', false)->isNotEmpty());
        $linked = $links->where('is_active', true)->pluck('group_id')->intersect($groupIds)->isNotEmpty();
        $granted = StudentContentGrant::where('student_id', $student->id)
            ->where('grantable_type', $resource->getMorphClass())->where('grantable_id', $resource->id)->exists();

        [$audienceOk, $audienceDetail] = match (true) {
            $resource->is_shared && ! $pausedAll => [true, 'مشترك مع كل مجموعات المادة'],
            $linked => [true, 'مشترك مع مجموعة الطالب'],
            $granted => [true, 'يحتفظ به الطالب بعد نقله من مجموعة أخرى'],
            $resource->is_shared && $pausedAll => [false, 'موقوف عن مجموعة الطالب'],
            $links->where('is_active', false)->pluck('group_id')->intersect($groupIds)->isNotEmpty() => [false, 'موقوف عن مجموعة الطالب'],
            default => [false, $groupIds === [] ? 'الطالب بدون مجموعة والمورد غير مشترك مع الجميع' : 'المورد غير مشترك مع مجموعة الطالب'],
        };
        $step($audienceOk, 'المورد موجّه لمجموعة الطالب', $audienceDetail);

        // 6. placements: at least one fully open path
        $placements = ResourcePlacement::where('subject_resource_id', $resource->id)->get();
        $openPath = false;
        $pathLines = [];

        foreach ($placements as $placement) {
            [$open, $why] = $this->pathState($placement, (int) $student->id);
            $openPath = $openPath || $open;
            $pathLines[] = ($open ? '✓ ' : '✗ ').$why;
        }

        $step($openPath, 'يظهر في مكان مفتوح للطالب',
            $placements->isEmpty() ? 'المورد غير موضوع في أي وحدة أو درس' : implode(' | ', $pathLines));

        $visible = $alive && $active && $enrolled && $notExcluded && $audienceOk && $openPath;

        $blocking = collect($steps)->firstWhere('ok', false);

        return [
            'visible' => $visible,
            'summary' => $visible ? 'الطالب يرى هذا المورد.' : 'الطالب لا يرى هذا المورد: '.$blocking['label'].($blocking['detail'] ? " — {$blocking['detail']}" : ''),
            'steps' => $steps,
        ];
    }

    /** @return array{0: bool, 1: string} */
    private function pathState(ResourcePlacement $placement, int $studentId): array
    {
        if ($placement->isGeneral()) {
            return [(bool) $placement->is_active, $placement->is_active ? 'المرفقات العامة' : 'المرفقات العامة (مخفي هنا)'];
        }

        $lesson = $placement->educational_lesson_id ? EducationalLesson::withTrashed()->find($placement->educational_lesson_id) : null;
        $unit = $lesson
            ? EducationalUnit::withTrashed()->with('stage')->find($lesson->educational_unit_id)
            : EducationalUnit::withTrashed()->with('stage')->find($placement->educational_unit_id);

        $label = trim(($unit?->name_ar ?? '؟').($lesson ? ' › '.$lesson->name_ar : ''));

        $excludedFrom = fn ($model) => $model && StudentContentExclusion::query()
            ->where('student_id', $studentId)
            ->where('excludable_type', $model->getMorphClass())
            ->where('excludable_id', $model->id)
            ->exists();

        $problem = match (true) {
            ! $placement->is_active => 'مخفي في هذا المكان',
            ! $unit || $unit->trashed() => 'الوحدة محذوفة',
            ! $unit->is_active => 'الوحدة موقوفة',
            ! $unit->stage?->is_active => 'المرحلة موقوفة',
            $excludedFrom($unit) => 'الطالب مستثنى من الوحدة',
            $lesson && $lesson->trashed() => 'الدرس محذوف',
            $lesson && ! $lesson->is_active => 'الدرس موقوف',
            $lesson && $excludedFrom($lesson) => 'الطالب مستثنى من الدرس',
            default => null,
        };

        return [$problem === null, $problem ? "{$label} ({$problem})" : $label];
    }
}
