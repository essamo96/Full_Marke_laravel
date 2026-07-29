<?php

namespace App\Services;

use App\Models\Group;
use App\Models\Registration;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Financial figures for a single teacher.
 *
 * Shared by the finance section and the dashboard panel so both report the
 * same numbers. "Collected" always means money on a payment the administration
 * has marked confirmed: a registration's amount_paid column also counts
 * payments still awaiting review, so figures are derived from
 * payment_registrations joined to confirmed payments instead.
 */
class TeacherFinanceReport
{
    /** Registration statuses that represent a real seat in a group. */
    public const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function __construct(private Teacher $teacher)
    {
    }

    public static function for(Teacher $teacher): self
    {
        return new self($teacher);
    }

    /** @return \Illuminate\Database\Eloquent\Builder */
    public function groupsQuery()
    {
        return Group::where('teacher_id', $this->teacher->id);
    }

    public function ownsGroup(Group $group): bool
    {
        return $group->teacher_id === $this->teacher->id;
    }

    /** Active registrations across the given groups. */
    public function registrationsFor(Collection $groupIds, array $with = []): Collection
    {
        return Registration::whereIn('group_id', $groupIds)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with($with)
            ->get();
    }

    /**
     * Confirmed amount collected, keyed by registration id.
     *
     * @param  array<int>  $registrationIds
     */
    public function confirmedPaidByRegistration(array $registrationIds): Collection
    {
        if (empty($registrationIds)) {
            return collect();
        }

        return DB::table('payment_registrations as pr')
            ->join('payments as p', 'p.id', '=', 'pr.payment_id')
            ->where('p.status', 'confirmed')
            ->whereIn('pr.registration_id', $registrationIds)
            ->groupBy('pr.registration_id')
            ->selectRaw('pr.registration_id, SUM(pr.allocated_amount) as paid')
            ->pluck('paid', 'pr.registration_id')
            ->map(fn ($v) => (float) $v);
    }

    /**
     * Roll registrations up into expected / collected / outstanding.
     *
     * @return array{expected: float, collected: float, outstanding: float, students: int, rate: int}
     */
    public function summarise(Collection $registrations, Collection $paidMap): array
    {
        $expected = 0.0;
        $collected = 0.0;

        foreach ($registrations as $registration) {
            $expected += (float) $registration->fee_snapshot;
            $collected += (float) ($paidMap[$registration->id] ?? 0);
        }

        return [
            'expected' => $expected,
            'collected' => $collected,
            'outstanding' => max(0, $expected - $collected),
            'students' => $registrations->count(),
            'rate' => $expected > 0 ? (int) round(($collected / $expected) * 100) : 0,
        ];
    }

    /** Attach per-registration financial figures for the views. */
    public function decorate(Collection $registrations, Collection $paidMap): Collection
    {
        return $registrations->map(function ($registration) use ($paidMap) {
            $fee = (float) $registration->fee_snapshot;
            $paid = (float) ($paidMap[$registration->id] ?? 0);

            $registration->confirmed_paid = $paid;
            $registration->confirmed_outstanding = max(0, $fee - $paid);
            $registration->confirmed_progress = $fee > 0 ? min(100, (int) round(($paid / $fee) * 100)) : 0;
            // Money handed over that admin has not signed off yet.
            $registration->pending_amount = max(0, (float) $registration->amount_paid - $paid);

            return $registration;
        });
    }

    /**
     * Per-group rows plus the overall totals, in one pass.
     *
     * @return array{totals: array, rows: Collection}
     */
    public function groupBreakdown(): array
    {
        $groups = $this->groupsQuery()->with('subject')->orderByDesc('created_at')->get();
        $registrations = $this->registrationsFor($groups->pluck('id'));
        $paidMap = $this->confirmedPaidByRegistration($registrations->pluck('id')->all());
        $byGroup = $registrations->groupBy('group_id');

        $rows = $groups->map(function ($group) use ($byGroup, $paidMap) {
            $summary = $this->summarise($byGroup->get($group->id, collect()), $paidMap);

            return (object) array_merge($summary, [
                'group' => $group,
                'collection_rate' => $summary['rate'],
            ]);
        });

        return [
            'totals' => $this->summarise($registrations, $paidMap),
            'rows' => $rows,
        ];
    }

    /**
     * Enrolments with money still owed, worst first — what the dashboard
     * panel needs to surface for chasing.
     */
    public function topOutstanding(int $limit = 5): Collection
    {
        $groupIds = $this->groupsQuery()->pluck('id');
        $registrations = $this->registrationsFor($groupIds, ['student', 'group']);
        $paidMap = $this->confirmedPaidByRegistration($registrations->pluck('id')->all());

        return $this->decorate($registrations, $paidMap)
            ->filter(fn ($r) => $r->confirmed_outstanding > 0)
            ->sortByDesc('confirmed_outstanding')
            ->take($limit)
            ->values();
    }

    /**
     * Confirmed payment rows limited to this teacher's groups.
     *
     * Selected off payment_registrations because one payment can be split
     * across several enrolments, and only the slice landing on this teacher's
     * groups is theirs to see.
     */
    public function confirmedPaymentsQuery(?array $groupIds = null)
    {
        $groupIds = $groupIds ?? $this->groupsQuery()->pluck('id')->all();

        return DB::table('payment_registrations as pr')
            ->join('payments as p', 'p.id', '=', 'pr.payment_id')
            ->join('registrations as r', 'r.id', '=', 'pr.registration_id')
            ->join('students as s', 's.id', '=', 'r.student_id')
            ->join('groups as g', 'g.id', '=', 'r.group_id')
            ->where('p.status', 'confirmed')
            ->whereIn('r.group_id', $groupIds ?: [0])
            ->orderByDesc('p.reviewed_at')
            ->select([
                'pr.allocated_amount',
                'p.payment_number',
                'p.invoice_number',
                'p.method',
                'p.reviewed_at',
                'p.created_at',
                's.full_name_ar as student_name_ar',
                's.full_name_en as student_name_en',
                'g.name as group_name',
                'g.id as group_id',
            ]);
    }

    /** Grand total of confirmed money across the teacher's groups. */
    public function totalConfirmed(?array $groupIds = null): float
    {
        $groupIds = $groupIds ?? $this->groupsQuery()->pluck('id')->all();

        return (float) DB::table('payment_registrations as pr')
            ->join('payments as p', 'p.id', '=', 'pr.payment_id')
            ->join('registrations as r', 'r.id', '=', 'pr.registration_id')
            ->where('p.status', 'confirmed')
            ->whereIn('r.group_id', $groupIds ?: [0])
            ->sum('pr.allocated_amount');
    }
}
