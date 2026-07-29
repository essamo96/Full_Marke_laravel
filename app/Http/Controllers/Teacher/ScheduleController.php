<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Services\WeeklySchedule;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    public function index()
    {
        $teacher = Auth::guard('teacher')->user();

        $groups = WeeklySchedule::decorate(
            Group::where('teacher_id', $teacher->id)->with('subject')->get()
        );

        $groupsByDay = WeeklySchedule::bucketByDay($groups);
        $todayKey = WeeklySchedule::todayKey();

        $todayCount = $groupsByDay[$todayKey]->count();
        // A group meeting three days a week is three sessions in the week.
        $weeklyCount = $groups->sum(fn ($g) => count($g->days ?? []));

        return view('teacher.schedule.index', compact(
            'groups', 'groupsByDay', 'todayKey', 'todayCount', 'weeklyCount'
        ));
    }
}
