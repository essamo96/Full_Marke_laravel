<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PaymentRejectRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class ApprovalsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'approvals';
        $this->path = 'approvals';
    }

    public function getIndex(Request $request)
    {
        // Mark notifications as read
        if (auth('admin')->check()) {
            auth('admin')->user()->unreadNotifications
                ->where('type', \App\Notifications\NewPaymentSubmittedNotification::class)
                ->markAsRead();
        }

        $query = Payment::with(['student', 'paymentMethod', 'items.registration.subject'])
            ->pending()
            ->latest();

        if ($request->filled('payment_number')) {
            $query->where(function($q) use ($request) {
                $q->where('payment_number', 'like', '%' . $request->payment_number . '%')
                  ->orWhere('id', $request->payment_number);
            });
        }
        if ($request->filled('student')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('full_name_ar', 'like', '%' . $request->student . '%')
                  ->orWhere('full_name_en', 'like', '%' . $request->student . '%')
                  ->orWhere('email', 'like', '%' . $request->student . '%')
                  ->orWhere('phone', 'like', '%' . $request->student . '%');
            });
        }
        if ($request->filled('method')) {
            $query->where('method', $request->method);
        }
        if ($request->filled('amount')) {
            $query->where('amount', $request->amount);
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $payments = $query->paginate(15)->appends($request->all());
        $methods = \App\Models\PaymentMethod::all();

        return view('admin.approvals.view', self::$data + ['payments' => $payments, 'methods' => $methods]);
    }

    public function postConfirm(Request $request, PaymentService $service)
    {
        try {
            $payment = Payment::pending()->findOrFail(Crypt::decrypt($request->id));
        } catch (\Exception $e) {
            return redirect()->route('approvals.view')->with('danger', __('app.not_found'));
        }

        $service->confirm($payment, auth('admin')->user());

        return redirect()->route('approvals.view')->with('success', __('app.confirm_success'));
    }

    public function postReject(PaymentRejectRequest $request, PaymentService $service)
    {
        try {
            $payment = Payment::pending()->findOrFail(Crypt::decrypt($request->id));
        } catch (\Exception $e) {
            return redirect()->route('approvals.view')->with('danger', __('app.not_found'));
        }

        $service->reject($payment, auth('admin')->user(), $request->rejection_reason);

        return redirect()->route('approvals.view')->with('success', __('app.reject_success'));
    }
}
