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
                'subject_id' => $group->subject_id,
                'subject_name' => $group->subject->name,
                'group_id' => $group->id,
                'group_name' => $group->name,
                'fee' => $group->subject->fee,
                'join_code' => $code,
                'message' => 'أنت غير مسجل في المادة التابعة لهذه المجموعة أو لم تقم بتأكيد دفعاتك المالية. الرسوم المستحقة للانضمام هي ' . $group->subject->fee . ' JOD.'
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
            return redirect()->route('student.groups')->withErrors(['error' => 'تم إيقاف حالتك في هذه المجموعة من قبل الإدارة. يرجى التواصل مع الإدارة لمزيد من التفاصيل.']);
        }

        $group->load('teacher', 'subject');
        
        $subject = $group->subject;
        $subject->load([
            'stages' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'stages.units' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'stages.units.lessons' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            },
            'stages.units.lessons.resources' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }
        ]);

        // Also fetch general resources that are not attached to any lesson
        $generalResources = \App\Models\SubjectResource::where('subject_id', $subject->id)
            ->where('is_active', true)
            ->whereNull('educational_lesson_id')
            ->orderBy('sort_order')
            ->get();

        $notes = $group->notes()->latest()->get();

        return view('student.groups.show', compact('group', 'subject', 'generalResources', 'notes'));
    }

    public function registerAndJoinDirectly(Request $request, \App\Services\PaymentService $paymentService)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'group_id' => 'required|exists:groups,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'receipt' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'join_code' => 'required|string',
        ]);

        $student = Auth::guard('student')->user();
        $subject = \App\Models\Subject::findOrFail($request->subject_id);

        if (!$subject->is_active) {
            return response()->json(['success' => false, 'message' => 'التسجيل غير متاح حالياً لهذه المادة.'], 400);
        }
        if ($subject->reg_start_date && now()->lt($subject->reg_start_date)) {
            return response()->json(['success' => false, 'message' => 'لم يبدأ التسجيل لهذه المادة بعد.'], 400);
        }
        if ($subject->reg_end_date && now()->gt($subject->reg_end_date)) {
            return response()->json(['success' => false, 'message' => 'انتهى موعد التسجيل لهذه المادة.'], 400);
        }

        $alreadyActive = Registration::where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
            ->exists();

        if ($alreadyActive) {
            return response()->json(['success' => false, 'message' => 'أنت مسجل بالفعل في هذه المادة أو قمت بتقديم طلب مسبقاً.'], 400);
        }

        $minPayment = $subject->min_payment ?? $subject->fee;
        if ($request->amount < $minPayment) {
            return response()->json(['success' => false, 'message' => 'المبلغ المدفوع أقل من الحد الأدنى المطلوب (' . $minPayment . ' JOD).'], 400);
        }

        $group = Group::findOrFail($request->group_id);
        if (!$group->hasAvailableCapacity()) {
            return response()->json(['success' => false, 'message' => 'المجموعة ممتلئة. لا يوجد مقاعد متاحة.'], 400);
        }

        $joinCode = \App\Models\GroupJoinCode::where('code', $request->join_code)->first();
        if (!$joinCode || !$joinCode->isValid()) {
            return response()->json(['success' => false, 'message' => 'الكود غير صالح أو منتهي الصلاحية.'], 400);
        }

        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        \Illuminate\Support\Facades\DB::transaction(function () use ($student, $subject, $group, $request, $receiptPath, $paymentService, $joinCode) {
            // Create pending registration
            $registration = Registration::create([
                'registration_number' => Registration::generateNumber(),
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'group_id' => $group->id,
                'fee_snapshot' => $subject->fee,
                'amount_paid' => 0,
                'status' => 'pending',
            ]);

            // Create pending payment
            $payment = \App\Models\Payment::create([
                'payment_number' => \App\Models\Payment::generateNumber(),
                'student_id' => $student->id,
                'method' => (string) $request->payment_method_id,
                'amount' => $request->amount,
                'receipt_image' => $receiptPath,
                'notes' => 'مقدم عبر كود التشعيب للمجموعة: ' . $group->name,
                'status' => 'pending',
            ]);

            // Link payment to registration
            \App\Models\PaymentItem::create([
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
                'allocated_amount' => $request->amount,
            ]);

            // Notify Admin
            $paymentService->notifyPaymentSubmitted($payment->load('items'), $student);

            // Increment join code usage
            $joinCode->increment('used_count');
        });
        
        return response()->json(['success' => true, 'message' => 'تم تقديم طلب التسجيل ودفع الرسوم. سيتم تفعيل حسابك في المجموعة فور تأكيد الإدارة للدفع.']);
    }
}
