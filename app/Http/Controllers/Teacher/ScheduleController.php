<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    private const WEEKDAY_NUM = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $groups = Group::where('teacher_id', $teacher->id)
            ->with('subject')
            ->get()
            ->map(function ($group) {
                $group->remaining_lectures = $this->remainingLectures($group);
                $group->schedule_status = $this->scheduleStatus($group);

                return $group;
            });

        $groupsByDay = [];
        foreach (array_keys(self::WEEKDAY_NUM) as $day) {
            $groupsByDay[$day] = $groups->filter(fn ($g) => in_array($day, $g->days ?? []))
                ->sortBy('start_time')
                ->values();
        }

        return view('teacher.schedule.index', compact('groups', 'groupsByDay'));
    }

    private function remainingLectures(Group $group): ?int
    {
        if (! $group->end_date || empty($group->days)) {
            return null;
        }

        $start = now()->startOfDay();
        $end = Carbon::parse($group->end_date)->endOfDay();

        if ($end->lt($start)) {
            return 0;
        }

        $dayNumbers = collect($group->days)->map(fn ($d) => self::WEEKDAY_NUM[$d] ?? null)->filter()->all();

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

    private function scheduleStatus(Group $group): string
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
}
