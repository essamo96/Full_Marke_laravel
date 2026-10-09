<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupJoinCode;
use App\Models\Registration;
use App\Models\Group;
use App\Services\StudentContentGate;
use App\Services\StudentGroupTransferService;

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

        // Every registration without a group belongs here, regardless of
        // whether the subject currently has any groups defined yet — group
        // assignment is entirely admin/code-driven now, so a subject with no
        // groups created yet is just as "awaiting branching" as one that has
        // groups the student hasn't been placed into. Previously this filtered
        // out subjects with zero groups, silently hiding those registrations
        // from the page entirely.
        $withoutGroup = $registrations
            ->filter(fn ($registration) => ! $registration->group)
            ->values();

        return view('student.groups.index', compact('withGroup', 'withoutGroup'));
    }

    public function joinByCode(Request $request, StudentGroupTransferService $transferService)
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

        $subject = $group->subject;
        if (!$subject || !$subject->is_active) {
            return response()->json(['success' => false, 'message' => 'التسجيل غير متاح حالياً لهذه المادة.'], 400);
        }
        if ($subject->reg_start_date && now()->lt($subject->reg_start_date)) {
            return response()->json(['success' => false, 'message' => 'لم يبدأ التسجيل لهذه المادة بعد.'], 400);
        }
        if ($subject->reg_end_date && now()->gt($subject->reg_end_date)) {
            return response()->json(['success' => false, 'message' => 'انتهى موعد التسجيل لهذه المادة.'], 400);
        }

        if (!$group->hasAvailableCapacity()) {
            return response()->json(['success' => false, 'message' => 'المجموعة ممتلئة. تم تجاوز الحد الأقصى للمستخدمين.'], 400);
        }

        $student = Auth::guard('student')->user();

        if (!$student->status) {
            return response()->json(['success' => false, 'message' => 'حسابك غير فعال حالياً. يرجى التواصل مع الإدارة.'], 400);
        }

        // The student must already be registered in the subject (pending, partially_paid, or fully_paid).
        $registration = Registration::where('student_id', $student->id)
            ->where('subject_id', $group->subject_id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->first();

        if (!$registration) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن استخدام الكود: يجب أن تكون مسجلاً في المادة التابعة لهذه المجموعة أولاً. يرجى التواصل مع الإدارة.'
            ], 400);
        }

        if ($registration->group_id === $group->id) {
            return response()->json(['success' => false, 'message' => 'أنت مسجل في هذه المجموعة بالفعل.'], 400);
        }

        try {
            $transferService->transfer($registration, $group);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }

        $joinCode->increment('used_count');

        return response()->json(['success' => true, 'message' => 'تم الانضمام للمجموعة بنجاح مع الاحتفاظ بالمحتوى السابق إن وُجد.']);
    }

    public function show(Group $group)
    {
        $student = Auth::guard('student')->user();

        // Ensure the student is registered in this subject and specifically assigned to this group
        $registration = $student->registrations()
            ->where('subject_id', $group->subject_id)
            ->where('group_id', $group->id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->first();

        if (!$registration) {
            return redirect()->route('student.groups')->withErrors(['error' => 'أنت غير مسجل في هذه المجموعة.']);
        }

        if ($registration->group_status === \App\Models\Registration::GROUP_STATUS_SUSPENDED) {
            return redirect()->route('student.groups')->withErrors([
                'error' => 'تم إيقاف حالتك في هذه المجموعة من قبل الإدارة لحين تسديد الرسوم المتبقية البالغة '
                    . number_format($registration->remaining_amount, 2)
                    . '. يرجى التواصل مع الإدارة لمزيد من التفاصيل.',
            ]);
        }

        $group->load('teacher', 'subject');

        $subject = $group->subject;
        $studentId = (int) $student->id;
        $groupId = (int) $group->id;

        // What this student may see of the subject, from the one gate every student surface uses:
        // kill switch, group audience (shared / linked / paused), personal grants, exclusions at
        // resource / lesson / unit level, and open placements - empty lessons and units are dropped.
        $tree = app(StudentContentGate::class)->tree($studentId, $subject, [$groupId]);
        $units = $tree['units'];
        $generalResources = $tree['general'];


        $allNotes = $group->notes()
            ->where(function ($q) use ($student) {
                $q->whereNull('student_id')->orWhere('student_id', $student->id);
            })
            ->latest()
            ->get();

        // Group-wide announcements vs. the teacher's personal notes for this student
        $notes = $allNotes->whereNull('student_id')->values();
        $studentNotes = $allNotes->whereNotNull('student_id')->values();

        // Exam schedule: current group exams + exams retained via transfer grants.
        $grantedExamIds = app(\App\Services\StudentExamGrantService::class)
            ->grantedExamIdsForStudent($studentId);

        $exams = \App\Models\Exam::query()
            ->where(function ($q) use ($groupId, $grantedExamIds) {
                $q->forGroups([$groupId]);
                if ($grantedExamIds->isNotEmpty()) {
                    $q->orWhereIn('exams.id', $grantedExamIds);
                }
            })
            ->orderByDesc('start_time')
            ->get()
            // excluded_student_ids can hold ints or numeric strings: compare in PHP, not with JSON containment
            ->reject(fn ($exam) => $exam->isStudentExcluded((int) $studentId))
            ->values();

        $grades = \App\Models\Grade::where('student_id', $student->id)
            ->whereIn('exam_id', $exams->pluck('id'))
            ->get()
            ->keyBy('exam_id');

        return view('student.groups.show', compact(
            'group', 'subject', 'units', 'generalResources', 'notes', 'studentNotes', 'exams', 'grades'
        ));
    }
}
