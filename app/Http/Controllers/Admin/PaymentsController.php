<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\Program;
use Illuminate\Http\Request;

class PaymentsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'payments';
        $this->path = 'payments';
    }

    public function getIndex(Request $request)
    {
        $query = Payment::with(['student', 'paymentMethod', 'items.registration.subject.program']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        if ($request->filled('program_id')) {
            $query->whereHas('items.registration.subject', function ($q) use ($request) {
                $q->where('program_id', $request->program_id);
            });
        }

        $payments = $query->latest()->paginate(20)->withQueryString();

        return view('admin.payments.view', self::$data + [
            'payments' => $payments,
            'programs' => Program::orderBy('order')->get(),
        ]);
    }
}
