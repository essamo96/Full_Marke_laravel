<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GroupsController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $registrations = $student->registrations()
            ->with(['subject.groups', 'group.teacher'])
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->get();

        $withGroup = $registrations->filter(fn ($registration) => $registration->group)->values();

        $withoutGroup = $registrations
            ->filter(fn ($registration) => ! $registration->group && $registration->subject->groups->isNotEmpty())
            ->values();

        return view('student.groups.index', compact('withGroup', 'withoutGroup'));
    }
}
