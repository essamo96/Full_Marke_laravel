<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        $registrations = $student->registrations()
            ->with(['subject.program', 'subject.groups', 'group.teacher'])
            ->latest()
            ->get();

        $totalFee = (float) $registrations->sum('fee_snapshot');
        $totalPaid = (float) $registrations->sum('amount_paid');
        $totalRemaining = max(0, $totalFee - $totalPaid);
        $paidPercent = $totalFee > 0 ? (int) round(($totalPaid / $totalFee) * 100) : 0;

        $programsCount = $registrations->pluck('subject.program_id')->unique()->count();
        $pendingCount = $registrations->where('status', 'pending')->count();
        $activeCount = $registrations->whereIn('status', ['partially_paid', 'fully_paid'])->count();
        $fullyPaidCount = $registrations->where('status', 'fully_paid')->count();

        // Registrations that still need a group but have one available to pick —
        // used to nudge the student toward the (optional) group selection.
        $needsGroupCount = $registrations->filter(function ($registration) {
            return is_null($registration->group_id) && $registration->subject->groups->isNotEmpty();
        })->count();

        // Real weekly schedule built from the groups the student actually joined.
        // Day codes match the admin group form (sat, sun, mon, tue, wed, thu, fri).
        $dayOrder = ['sun' => 0, 'mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6];
        $upcomingSessions = $registrations
            ->filter(fn ($registration) => $registration->group && $registration->group->is_active)
            ->map(function ($registration) use ($dayOrder) {
                $group = $registration->group;
                $days = collect($group->days ?? [])->map(fn ($day) => (string) $day);

                return [
                    'subject' => $registration->subject,
                    'group' => $group,
                    'days' => $days,
                    'sort_key' => $days->map(fn ($day) => $dayOrder[$day] ?? 99)->min() ?? 99,
                ];
            })
            ->sortBy('sort_key')
            ->take(4)
            ->values();

        $recentPayments = $student->payments()->latest()->take(5)->get();

        return view('student.dashboard.index', [
            'student' => $student,
            'registrations' => $registrations,
            'totalFee' => $totalFee,
            'totalPaid' => $totalPaid,
            'totalRemaining' => $totalRemaining,
            'paidPercent' => $paidPercent,
            'programsCount' => $programsCount,
            'pendingCount' => $pendingCount,
            'activeCount' => $activeCount,
            'fullyPaidCount' => $fullyPaidCount,
            'needsGroupCount' => $needsGroupCount,
            'upcomingSessions' => $upcomingSessions,
            'recentPayments' => $recentPayments,
            'paymentMethods' => \App\Models\PaymentMethod::active()->orderBy('sort_order')->get(),
        ]);
    }
}
