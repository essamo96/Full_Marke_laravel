<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Student;
use App\Models\Registration;
use App\Models\Program;
use App\Models\Subject;

class EnrolmentController extends AdminController
{
    public function __construct() {
        parent::__construct();
        self::$data['active_menu'] = 'enrolments';
    }

    public function index()
    {
        $programs = Program::active()->get();
        $subjects = Subject::active()->get();
        $groups = Group::active()->get();

        return view('admin.enrolment.index', self::$data + compact('programs', 'subjects', 'groups'));
    }

    public function getStudents(Request $request)
    {
        $query = Student::active()
            ->whereNotNull('email_verified_at')
            ->whereDoesntHave('payments', function ($q) {
                $q->where('status', 'pending');
            })
            ->whereDoesntHave('registrations', function ($q) {
                $q->join('subjects', 'registrations.subject_id', '=', 'subjects.id')
                  ->whereColumn('registrations.amount_paid', '<', 'subjects.min_payment')
                  ->where('subjects.min_payment', '>', 0);
            });

        if ($request->filled('program_id')) {
            $query->whereHas('registrations.subject', function($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('subject_id')) {
            $query->whereHas('registrations', function($q) use ($request) {
                $q->where('subject_id', $request->subject_id);
            });
        }

        if ($request->filled('group_id')) {
            $query->whereHas('registrations', function($q) use ($request) {
                $q->where('group_id', $request->group_id);
            });
        }

        // Used by the "transfer to new subject" flow: hide students already registered in the target subject
        if ($request->filled('exclude_subject_id')) {
            $query->whereDoesntHave('registrations', function($q) use ($request) {
                $q->where('subject_id', $request->exclude_subject_id);
            });
        }

        $students = $query->with('registrations.group', 'registrations.subject')->get();
        
        if ($request->filled('target_group_id')) {
            $targetGroup = Group::find($request->target_group_id);
            if ($targetGroup) {
                $students->transform(function ($student) use ($targetGroup) {
                    $student->has_conflict = $this->checkConflict($student, $targetGroup);
                    return $student;
                });
            }
        }

        return response()->json($students);
    }

    public function enroll(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'target_group_id' => 'nullable|exists:groups,id',
            'target_subject_id' => 'required_without:target_group_id|exists:subjects,id',
        ]);

        $targetGroup = $request->filled('target_group_id') ? Group::findOrFail($request->target_group_id) : null;
        $subject = $targetGroup ? $targetGroup->subject : Subject::findOrFail($request->target_subject_id);
        $subjectId = $subject->id;
        $successCount = 0;
        $failedStudents = [];

        foreach ($request->student_ids as $studentId) {
            $student = Student::find($studentId);

            if ($targetGroup && $this->checkConflict($student, $targetGroup)) {
                $failedStudents[] = ['id' => $studentId, 'reason' => 'تعارض في المواعيد'];
                continue;
            }

            $existingReg = Registration::where('student_id', $studentId)
                ->where('subject_id', $subjectId)
                ->first();

            if (!$existingReg) {
                // Auto register and create payment dues (group assignment is optional)
                $registration = Registration::create([
                    'student_id' => $studentId,
                    'subject_id' => $subjectId,
                    'group_id' => $targetGroup->id ?? null,
                    'registration_number' => Registration::generateNumber(),
                    'fee_snapshot' => $subject->fee ?? 0,
                    'status' => 'pending',
                ]);

                $minPayment = $subject->min_payment ?? 0;
                if ($minPayment > 0) {
                    $payment = \App\Models\Payment::create([
                        'student_id' => $studentId,
                        'payment_number' => \App\Models\Payment::generateNumber(),
                        'amount' => $minPayment,
                        'method' => \App\Models\PaymentMethod::first()->id ?? 1,
                        'receipt_image' => '',
                        'status' => 'pending',
                        'notes' => 'رسوم مستحقة عند التسجيل في مادة ' . ($subject->name ?? ''),
                    ]);

                    \App\Models\PaymentItem::create([
                        'payment_id' => $payment->id,
                        'registration_id' => $registration->id,
                        'allocated_amount' => $minPayment,
                    ]);
                }

                $message = $targetGroup
                    ? ('تم تسجيلك وتشعيبك في مجموعة ' . ($subject->name ?? '') . ($minPayment > 0 ? ' ويوجد رسوم مستحقة بقيمة ' . $minPayment : ''))
                    : ('تم تسجيلك في مادة ' . ($subject->name ?? '') . ' وسيتم تشعيبك في مجموعة لاحقاً' . ($minPayment > 0 ? '. يوجد رسوم مستحقة بقيمة ' . $minPayment : ''));

                \Illuminate\Support\Facades\Notification::send(
                    $student,
                    new \App\Notifications\StudentFeeDuesNotification($studentId, $message)
                );

                $successCount++;
            } elseif ($targetGroup) {
                // Existing registration: branch for the first time, or move between groups of the same subject
                if ($existingReg->group_id == $targetGroup->id) {
                    $failedStudents[] = ['id' => $studentId, 'reason' => 'الطالب مسجل بالفعل في هذه المجموعة'];
                    continue;
                }

                $previousGroup = $existingReg->group_id ? Group::find($existingReg->group_id) : null;
                $existingReg->group_id = $targetGroup->id;
                $existingReg->save();

                $message = $previousGroup
                    ? ('تم نقلك من مجموعة ' . $previousGroup->name . ' إلى مجموعة ' . $targetGroup->name . ' لمادة ' . ($subject->name ?? ''))
                    : ('تم تشعيبك في مجموعة ' . $targetGroup->name . ' لمادة ' . ($subject->name ?? ''));

                \Illuminate\Support\Facades\Notification::send(
                    $student,
                    new \App\Notifications\StudentFeeDuesNotification($studentId, $message)
                );

                $successCount++;
            } else {
                // Already registered in the subject and no group given: nothing to do
                $failedStudents[] = ['id' => $studentId, 'reason' => 'الطالب مسجل بالفعل في هذه المادة'];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم تشعيب/نقل $successCount طالب بنجاح.",
            'failed' => $failedStudents
        ]);
    }

    private function checkConflict($student, $targetGroup)
    {
        $targetDays = is_array($targetGroup->days) ? $targetGroup->days : json_decode($targetGroup->days, true) ?? [];
        $targetStart = $targetGroup->start_time;
        $targetEnd = $targetGroup->end_time;

        if (empty($targetDays) || !$targetStart || !$targetEnd) return false;

        $registrations = Registration::where('student_id', $student->id)
            ->whereNotNull('group_id')
            ->where('subject_id', '!=', $targetGroup->subject_id)
            ->with('group')
            ->get();

        foreach ($registrations as $reg) {
            $group = $reg->group;
            if (!$group) continue;
            
            $days = is_array($group->days) ? $group->days : json_decode($group->days, true) ?? [];
            $intersectDays = array_intersect($targetDays, $days);
            
            if (count($intersectDays) > 0) {
                if ($targetStart < $group->end_time && $targetEnd > $group->start_time) {
                    return true;
                }
            }
        }
        
        return false;
    }

    private function checkMinFee($student, $subjectId)
    {
        // Deprecated, logic moved to getStudents query
        return true;
    }
}
