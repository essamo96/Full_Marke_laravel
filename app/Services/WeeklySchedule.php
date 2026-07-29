<?php

namespace App\Services;

use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Weekly timetable maths, shared by the teacher and student schedule screens
 * so both sides agree on day ordering, status and remaining-lecture counts.
 */
class WeeklySchedule
{
    /** Day key => PHP dayOfWeek number (Carbon: Sunday = 0). */
    public const WEEKDAY_NUM = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

    public const DAY_LABELS_AR = [
        'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء',
        'thu' => 'الخميس', 'fri' => 'الجمعة', 'sat' => 'السبت',
    ];

    public const DAY_LABELS_EN = [
        'sun' => 'Sunday', 'mon' => 'Monday', 'tue' => 'Tuesday', 'wed' => 'Wednesday',
        'thu' => 'Thursday', 'fri' => 'Friday', 'sat' => 'Saturday',
    ];

    public const DAY_SHORT_AR = [
        'sun' => 'أحد', 'mon' => 'إثنين', 'tue' => 'ثلاثاء', 'wed' => 'أربعاء',
        'thu' => 'خميس', 'fri' => 'جمعة', 'sat' => 'سبت',
    ];

    /** Today's day key, e.g. 'tue'. */
    public static function todayKey(): string
    {
        return array_search(now()->dayOfWeek, self::WEEKDAY_NUM, true) ?: 'sun';
    }

    /**
     * Bucket groups into the seven day keys, each sorted by start time.
     *
     * @return array<string, Collection>
     */
    public static function bucketByDay(Collection $groups): array
    {
        $byDay = [];

        foreach (array_keys(self::WEEKDAY_NUM) as $day) {
            $byDay[$day] = $groups
                ->filter(fn ($g) => in_array($day, $g->days ?? [], true))
                ->sortBy('start_time')
                ->values();
        }

        return $byDay;
    }

    /** Decorate each group with schedule_status and remaining_lectures. */
    public static function decorate(Collection $groups): Collection
    {
        return $groups->map(function ($group) {
            $group->schedule_status = self::status($group);
            $group->remaining_lectures = self::remainingLectures($group);

            return $group;
        });
    }

    public static function status(Group $group): string
    {
        $today = now()->startOfDay();

        if ($group->start_date && $today->lt(Carbon::parse($group->start_date)->startOfDay())) {
            return 'upcoming';
        }

        if ($group->end_date && $today->gt(Carbon::parse($group->end_date)->endOfDay())) {
            return 'ended';
        }

        return 'active';
    }

    /**
     * How many meetings remain between today and the group's end date.
     */
    public static function remainingLectures(Group $group): ?int
    {
        if (! $group->end_date || empty($group->days)) {
            return null;
        }

        $start = now()->startOfDay();
        $end = Carbon::parse($group->end_date)->endOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        // Sunday maps to 0, so a bare filter() would silently drop it and
        // undercount every group that meets on a Sunday.
        $dayNumbers = collect($group->days)
            ->map(fn ($d) => self::WEEKDAY_NUM[$d] ?? null)
            ->filter(fn ($n) => $n !== null)
            ->all();

        if (empty($dayNumbers)) {
            return null;
        }

        $count = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (in_array($cursor->dayOfWeek, $dayNumbers, true)) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }

    /**
     * Whether a group's session is happening right now — drives the "live"
     * indicator. Only meaningful on a day the group actually meets.
     */
    public static function isLiveNow(Group $group): bool
    {
        if (! in_array(self::todayKey(), $group->days ?? [], true)) {
            return false;
        }

        if (self::status($group) !== 'active' || ! $group->start_time || ! $group->end_time) {
            return false;
        }

        $now = now()->format('H:i:s');

        return $now >= self::normaliseTime($group->start_time)
            && $now <= self::normaliseTime($group->end_time);
    }

    /** Times are stored inconsistently as H:i or H:i:s; compare on H:i:s. */
    public static function normaliseTime(?string $time): string
    {
        if (! $time) {
            return '00:00:00';
        }

        return substr_count($time, ':') === 1 ? $time . ':00' : $time;
    }

    /** 24h stored time rendered as a short 12h label, e.g. "4:30 م". */
    public static function formatTime(?string $time): string
    {
        if (! $time) {
            return '—';
        }

        try {
            return Carbon::createFromFormat('H:i:s', self::normaliseTime($time))->format('g:i A');
        } catch (\Throwable) {
            return $time;
        }
    }
}
