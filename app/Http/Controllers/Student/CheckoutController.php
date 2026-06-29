<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\PaymentMethod;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function show()
    {
        $student = Auth::guard('student')->user();

        $items = CartItem::forUser($student->id, 'student')->with('subject')->get();

        if ($items->isEmpty()) {
            return redirect()->route('student.cart')->with('danger', __('app.cart_empty'));
        }

        $totalFee = $items->sum(fn (CartItem $i) => (float) $i->subject->fee);
        $totalMinPayment = $items->sum(fn (CartItem $i) => (float) ($i->subject->min_payment ?? $i->subject->fee));
        $paymentMethods = PaymentMethod::active()->orderBy('sort_order')->get();

        return view('student.checkout.show', compact('items', 'totalFee', 'totalMinPayment', 'paymentMethods'));
    }

    public function store(Request $request, PaymentService $service)
    {
        $student = Auth::guard('student')->user();

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'receipt' => 'required|file|mimes:png,jpg,jpeg,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
            'cart_item_ids' => 'required|array|min:1',
            'cart_item_ids.*' => 'exists:cart_items,id',
        ]);

        // Stored on the `local` disk, which is private (storage/app/private) —
        // never publicly served directly; admins view it via a signed URL.
        $receiptPath = $request->file('receipt')->store('receipts', 'local');

        try {
            $service->checkout(
                $student,
                $data['cart_item_ids'],
                (float) $data['amount'],
                (int) $data['payment_method_id'],
                $receiptPath,
                $data['notes'] ?? null
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('student.registrations')->with('success', __('app.checkout_success'));
    }
}
