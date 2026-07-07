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
        $totalRevenue = Payment::where('status', 'confirmed')->sum('total_amount');

        $totalOutstanding = Registration::where('registration_status', '!=', 'cancelled')
            ->get()
            ->sum(fn (Registration $r) => $r->remaining_amount);

        $revenueByProgram = Registration::query()
            ->join('subjects', 'subjects.id', '=', 'registrations.subject_id')
            ->join('programs', 'programs.id', '=', 'subjects.program_id')
            ->select('programs.title_ar', 'programs.title_en', DB::raw('SUM(registrations.amount_paid) as revenue'))
            ->groupBy('programs.id', 'programs.title_ar', 'programs.title_en')
            ->get();

        $revenueByMethod = Payment::query()
            ->where('status', 'confirmed')
            ->join('payment_methods', 'payment_methods.id', '=', 'payments.payment_method_id')
            ->select('payment_methods.name_ar', 'payment_methods.name_en', DB::raw('SUM(payments.total_amount) as revenue'))
            ->groupBy('payment_methods.id', 'payment_methods.name_ar', 'payment_methods.name_en')
            ->get();

        $monthlyRevenue = Payment::query()
            ->where('status', 'confirmed')
            ->where('confirmed_at', '>=', now()->subMonths(6))
            ->select(DB::raw("DATE_FORMAT(confirmed_at, '%Y-%m') as month"), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $debtors = Registration::with('student', 'subject')
            ->where('registration_status', '!=', 'cancelled')
            ->get()
            ->filter(fn (Registration $r) => $r->remaining_amount > 0)
            ->sortByDesc(fn (Registration $r) => $r->remaining_amount)
            ->values();

        return view('admin.financial_reports.view', self::$data + [
            'totalRevenue' => $totalRevenue,
            'totalOutstanding' => $totalOutstanding,
            'revenueByProgram' => $revenueByProgram,
            'revenueByMethod' => $revenueByMethod,
            'monthlyRevenue' => $monthlyRevenue,
            'debtors' => $debtors]);
    }
}
