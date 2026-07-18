<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupJoinCode;
use App\Models\Registration;
use App\Models\Group;

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

    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $code = trim($request->code);
        $joinCode = GroupJoinCode::where('code', $code)->first();

        if (!$joinCode || !$joinCode->isValid()) {
            return response()->json(['success' => false, 'message' => 'الكود غير صالح أو منتهي الصلاحية.'], 400);
        }

        $group = $joinCode->group;

        if (!$group || !$group->is_active) {
            return response()->json(['success' => false, 'message' => 'المجموعة غير متاحة حالياً.'], 400);
        }

        if (!$group->hasAvailableCapacity()) {
            return response()->json(['success' => false, 'message' => 'المجموعة ممتلئة. تم تجاوز الحد الأقصى للمستخدمين.'], 400);
        }

        $student = Auth::guard('student')->user();

        // Check if student is registered in the subject and has confirmed payments
        $registration = Registration::where('student_id', $student->id)
            ->where('subject_id', $group->subject_id)
            ->whereIn('status', ['partially_paid', 'fully_paid'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'needs_registration' => true,
                'program_url' => route('programs.show', $group->subject->program->slug ?? ''),
                'message' => 'أنت غير مسجل في المادة التابعة لهذه المجموعة أو لم تقم بتأكيد دفعاتك المالية.'
            ], 400);
        }

        if ($registration->group_id === $group->id) {
            return response()->json(['success' => false, 'message' => 'أنت مسجل في هذه المجموعة بالفعل.'], 400);
        }

        $oldGroupId = $registration->group_id;

        $registration->group_id = $group->id;
        $registration->save();

        if ($oldGroupId) {
            Group::where('id', $oldGroupId)->decrement('current_count');
        }
        $group->increment('current_count');
        $joinCode->increment('used_count');

        return response()->json(['success' => true, 'message' => 'تم الانضمام للمجموعة بنجاح.']);
    }
}
