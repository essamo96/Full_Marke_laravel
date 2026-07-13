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

    public function getIndex()
    {
        // Mark notifications as read
        if (auth('admin')->check()) {
            auth('admin')->user()->unreadNotifications
                ->where('type', \App\Notifications\NewPaymentSubmittedNotification::class)
                ->markAsRead();
        }

        $payments = Payment::with(['student', 'paymentMethod', 'items.registration.subject'])
            ->pending()
            ->latest()
            ->paginate(15);

        return view('admin.approvals.view', self::$data + ['payments' => $payments]);
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
