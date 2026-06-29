<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Support\Facades\Storage;

class ReceiptController extends Controller
{
    /**
     * Stream a payment receipt from the private `local` disk.
     * Only reachable via a temporary signed URL (see {@see Payment} usage
     * in admin.approvals/payments views) — never linked publicly.
     */
    public function show(Payment $payment)
    {
        abort_unless(Storage::disk('local')->exists($payment->receipt_image), 404);

        return Storage::disk('local')->response($payment->receipt_image);
    }
}
