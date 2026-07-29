<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\WeeklySchedule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /** Registration statuses that give the student a real seat in a group. */
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $student = Auth::guard('student')->user();

        // A student's timetable is the groups behind their active enrolments.
        // Registrations awaiting group assignment are surfaced separately
        // rather than silently dropped, so the page explains an empty week.
        $registrations = $student->registrations()
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with(['group.subject', 'group.teacher', 'subject'])
            ->get();

        $groups = WeeklySchedule::decorate(
            $registrations->pluck('group')->filter()->unique('id')->values()
        );

        $awaitingGroup = $registrations->filter(fn ($r) => ! $r->group)->values();

        $groupsByDay = WeeklySchedule::bucketByDay($groups);
        $todayKey = WeeklySchedule::todayKey();

        $todayCount = $groupsByDay[$todayKey]->count();
        $weeklyCount = $groups->sum(fn ($g) => count($g->days ?? []));

        return view('student.schedule.index', compact(
            'groups', 'groupsByDay', 'todayKey', 'todayCount', 'weeklyCount', 'awaitingGroup'
        ));
    }
}
