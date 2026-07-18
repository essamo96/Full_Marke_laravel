<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;

class FinancialReportsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'financial_reports';
        $this->path = 'financial_reports';
    }

    public function getIndex()
    {
        $totalRevenue = Payment::where('status', 'confirmed')->sum('amount');

        $totalOutstanding = Registration::where('status', '!=', 'cancelled')
            ->get()
            ->sum(fn (Registration $r) => $r->remaining_amount);

        $revenueByProgram = Registration::query()
            ->join('subjects', 'subjects.id', '=', 'registrations.subject_id')
            ->join('programs', 'programs.id', '=', 'subjects.program_id')
            ->select('programs.name_ar', 'programs.name_en', DB::raw('SUM(registrations.amount_paid) as revenue'))
            ->groupBy('programs.id', 'programs.name_ar', 'programs.name_en')
            ->get();

        $revenueByMethod = Payment::query()
            ->where('status', 'confirmed')
            ->select('method', DB::raw('SUM(amount) as revenue'), DB::raw('COUNT(*) as count'))
            ->groupBy('method')
            ->get();

        $monthlyRevenue = Payment::query()
            ->where('status', 'confirmed')
            ->whereNotNull('reviewed_at')
            ->where('reviewed_at', '>=', now()->subMonths(6))
            ->select(DB::raw("DATE_FORMAT(reviewed_at, '%Y-%m') as month"), DB::raw('SUM(amount) as revenue'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $debtors = Registration::with('student', 'subject')
            ->where('status', '!=', 'cancelled')
            ->get()
            ->filter(fn (Registration $r) => $r->remaining_amount > 0)
            ->sortByDesc(fn (Registration $r) => $r->remaining_amount)
            ->values();

        $studentStats = [
            'active' => \App\Models\Student::active()->count(),
            'inactive' => \App\Models\Student::where('status', false)->count(),
            'email_verified' => \App\Models\Student::whereNotNull('email_verified_at')->count(),
            'email_unverified' => \App\Models\Student::whereNull('email_verified_at')->count(),
        ];

        return view('admin.financial_reports.view', self::$data + [
            'totalRevenue' => $totalRevenue,
            'totalOutstanding' => $totalOutstanding,
            'revenueByProgram' => $revenueByProgram,
            'revenueByMethod' => $revenueByMethod,
            'monthlyRevenue' => $monthlyRevenue,
            'debtors' => $debtors,
            'studentStats' => $studentStats
        ]);
    }
}
