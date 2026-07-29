<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Registration;
use App\Services\TeacherFinanceReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Per-teacher financial reporting.
 *
 * Every figure is scoped to groups owned by the authenticated teacher. The
 * arithmetic lives in TeacherFinanceReport so this section and the dashboard
 * panel cannot drift apart.
 */
class FinanceController extends Controller
{
    private function report(): TeacherFinanceReport
    {
        return TeacherFinanceReport::for(Auth::guard('teacher')->user());
    }

    /** Finance overview: headline totals, per-group breakdown, recent activity. */
    public function index()
    {
        $report = $this->report();
        ['totals' => $totals, 'rows' => $rows] = $report->groupBreakdown();

        $groupRows = $rows->sortByDesc('collected')->values();

        $recentPayments = $report->confirmedPaymentsQuery()->limit(8)->get();

        return view('teacher.finance.index', compact('totals', 'groupRows', 'recentPayments'));
    }

    /** Every group with its collection figures. */
    public function groups()
    {
        ['totals' => $totals, 'rows' => $groupRows] = $this->report()->groupBreakdown();

        return view('teacher.finance.groups', compact('groupRows', 'totals'));
    }

    /** One group: every enrolled student with what they owe and have paid. */
    public function group(Group $group)
    {
        $report = $this->report();
        abort_unless($report->ownsGroup($group), 403);

        $group->load('subject');

        $registrations = $report->registrationsFor(collect([$group->id]), ['student']);
        $paidMap = $report->confirmedPaidByRegistration($registrations->pluck('id')->all());

        $summary = $report->summarise($registrations, $paidMap);
        $rows = $report->decorate($registrations, $paidMap)
            ->sortByDesc('confirmed_outstanding')
            ->values();

        return view('teacher.finance.group', compact('group', 'rows', 'summary'));
    }

    /** Every student across every group, so dues can be chased in one place. */
    public function students(Request $request)
    {
        $report = $this->report();
        $groupIds = $report->groupsQuery()->pluck('id');

        $query = Registration::whereIn('group_id', $groupIds)
            ->whereIn('status', TeacherFinanceReport::ACTIVE_STATUSES)
            ->with(['student', 'group.subject']);

        $search = trim((string) $request->query('q'));
        if ($search !== '') {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('full_name_ar', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $registrations = $query->get();
        $paidMap = $report->confirmedPaidByRegistration($registrations->pluck('id')->all());

        $summary = $report->summarise($registrations, $paidMap);
        $rows = $report->decorate($registrations, $paidMap);

        if ($request->query('filter') === 'outstanding') {
            $rows = $rows->filter(fn ($r) => $r->confirmed_outstanding > 0);
        } elseif ($request->query('filter') === 'settled') {
            $rows = $rows->filter(fn ($r) => $r->confirmed_outstanding <= 0);
        }

        $rows = $rows->sortByDesc('confirmed_outstanding')->values();

        return view('teacher.finance.students', compact('rows', 'summary', 'search'));
    }

    /** A single enrolment: the payment history behind its figures. */
    public function registration(Registration $registration)
    {
        $report = $this->report();
        $registration->load(['student', 'group.subject', 'subject']);

        abort_unless($registration->group && $report->ownsGroup($registration->group), 403);

        $paidMap = $report->confirmedPaidByRegistration([$registration->id]);
        $row = $report->decorate(collect([$registration]), $paidMap)->first();

        $payments = DB::table('payment_registrations as pr')
            ->join('payments as p', 'p.id', '=', 'pr.payment_id')
            ->where('pr.registration_id', $registration->id)
            ->orderByDesc('p.created_at')
            ->select([
                'p.payment_number',
                'p.invoice_number',
                'p.method',
                'p.status',
                'p.created_at',
                'p.reviewed_at',
                'pr.allocated_amount',
            ])
            ->get();

        return view('teacher.finance.registration', compact('row', 'payments'));
    }

    /** Confirmed payment log across the teacher's groups. */
    public function payments()
    {
        $report = $this->report();
        $groupIds = $report->groupsQuery()->pluck('id')->all();

        $payments = $report->confirmedPaymentsQuery($groupIds)->paginate(25)->withQueryString();

        // Separate aggregate: the paginated query is ordered and sliced, so it
        // cannot also produce the grand total.
        $total = $report->totalConfirmed($groupIds);

        return view('teacher.finance.payments', compact('payments', 'total'));
    }
}
