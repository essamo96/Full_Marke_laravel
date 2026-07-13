<?php

namespace App\Services;

use App\Mail\NewPaymentAdminMail;
use App\Mail\PaymentConfirmedMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\PaymentRejectedMail;
use App\Models\Admin;
use App\Models\CartItem;
use App\Models\Group;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * Encapsulates the academy's financial business rules:
 *  - checkout validation (min payment, capacity, no duplicate active registration)
 *  - proportional allocation of one payment across multiple subjects/registrations
 *  - confirm/reject lifecycle that activates registrations and recomputes payment_status
 *
 * Every multi-row mutation runs inside a DB transaction per academy_system_analysis.md §5.
 */
class PaymentService
{
    /**
     * Build pending registrations + a pending payment from the student's cart,
     * allocating the paid amount proportionally across the cart's subjects.
     *
     * @return Payment
     */
    public function checkout(Student $student, array $cartItemIds, float $paidAmount, int $paymentMethodId, string $receiptPath, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($student, $cartItemIds, $paidAmount, $paymentMethodId, $receiptPath, $notes) {
            $cartItems = CartItem::query()
                ->forUser($student->id, 'student')
                ->whereIn('id', $cartItemIds)
                ->with('subject')
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['cart' => 'السلة فارغة.']);
            }

            $totalFee = (float) $cartItems->sum(fn (CartItem $item) => $item->subject->fee);
            $totalMinPayment = (float) $cartItems->sum(fn (CartItem $item) => $item->subject->min_payment ?? $item->subject->fee);

            if ($paidAmount < $totalMinPayment) {
                throw ValidationException::withMessages(['amount' => 'المبلغ المدفوع أقل من الحد الأدنى المطلوب.']);
            }

            if ($paidAmount > $totalFee) {
                throw ValidationException::withMessages(['amount' => 'المبلغ المدفوع أكبر من إجمالي الرسوم.']);
            }

            foreach ($cartItems as $item) {
                $alreadyActive = Registration::where('student_id', $student->id)
                    ->where('subject_id', $item->subject_id)
                    ->whereIn('status', ['pending', 'partially_paid', 'fully_paid'])
                    ->exists();

                if ($alreadyActive) {
                    throw ValidationException::withMessages(['subject' => "الطالب مسجل بالفعل في {$item->subject->name}."]);
                }

                if ($item->group_id) {
                    $group = Group::findOrFail($item->group_id);
                    if (! $group->hasAvailableCapacity()) {
                        throw ValidationException::withMessages(['group' => "المجموعة المختارة لمادة {$item->subject->name} ممتلئة."]);
                    }
                }
            }

            $payment = Payment::create([
                'payment_number' => Payment::generateNumber(),
                'student_id' => $student->id,
                'method' => (string) $paymentMethodId,
                'amount' => $paidAmount,
                'receipt_image' => $receiptPath,
                'notes' => $notes,
                'status' => 'pending',
            ]);

            $registrations = collect();

            foreach ($cartItems as $item) {
                $registration = Registration::create([
                    'registration_number' => Registration::generateNumber(),
                    'student_id' => $student->id,
                    'subject_id' => $item->subject_id,
                    'group_id' => $item->group_id,
                    'fee_snapshot' => $item->subject->fee,
                    'amount_paid' => 0,
                    'status' => 'pending',
                ]);

                $registrations->push($registration);
            }

            $this->allocateProportionally($payment, $registrations, $totalFee, $paidAmount);

            CartItem::query()->whereIn('id', $cartItems->pluck('id'))->delete();

            $this->notifyPaymentSubmitted($payment->load('items'), $student);

            return $payment;
        });
    }

    /**
     * Mail templates #2 and #3 from academy_system_analysis.md §"القوالب البريدية" —
     * confirms receipt to the student and alerts active admins of a new pending payment.
     */
    protected function notifyPaymentSubmitted(Payment $payment, Student $student): void
    {
        Mail::to($student->email)->send(new PaymentReceivedMail($payment));

        $adminEmails = Admin::active()->pluck('email');
        if ($adminEmails->isNotEmpty()) {
            Mail::to($adminEmails->all())->send(new NewPaymentAdminMail($payment));
        }

        // Notify admins in dashboard
        \Illuminate\Support\Facades\Notification::send(
            Admin::active()->get(),
            new \App\Notifications\NewPaymentSubmittedNotification($payment)
        );
    }

    /**
     * Distribute $paidAmount across $registrations proportionally to each
     * registration's total_fee share of $totalFee (per §5.2 of the spec).
     */
    protected function allocateProportionally(Payment $payment, $registrations, float $totalFee, float $paidAmount): void
    {
        if ($totalFee <= 0) {
            return;
        }

        foreach ($registrations as $registration) {
            $share = ((float) $registration->fee_snapshot / $totalFee) * $paidAmount;

            PaymentItem::create([
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
                'allocated_amount' => round($share, 2),
            ]);
        }
    }

    /**
     * Confirm a pending payment: activate every related registration, apply
     * its allocated share to amount_paid, and recompute payment_status.
     */
    public function confirm(Payment $payment, Admin $admin): void
    {
        DB::transaction(function () use ($payment, $admin) {
            $payment->update([
                'status' => 'confirmed',
                'confirmed_by' => $admin->id,
                'confirmed_at' => now(),
            ]);

            foreach ($payment->items()->with('registration')->get() as $item) {
                $registration = $item->registration;
                $registration->amount_paid = (float) $registration->amount_paid + (float) $item->allocated_amount;

                if ($registration->status === 'pending') {
                    $registration->status = $registration->amount_paid >= (float) $registration->fee_snapshot
                        ? 'fully_paid'
                        : 'partially_paid';

                    if ($registration->group_id) {
                        Group::where('id', $registration->group_id)->increment('current_count');
                    }
                } else {
                    $registration->status = $registration->amount_paid >= (float) $registration->fee_snapshot
                        ? 'fully_paid'
                        : 'partially_paid';
                }

                $registration->save();
            }
        });

        Mail::to($payment->student->email)->send(new PaymentConfirmedMail($payment));

        // Generate PDF Invoice
        \App\Jobs\GenerateInvoiceJob::dispatch($payment);
    }

    /**
     * Reject a pending payment. Registrations remain pending so the student
     * can re-submit a new payment for the same cart/subjects.
     */
    public function reject(Payment $payment, Admin $admin, string $reason): void
    {
        $payment->update([
            'status' => 'rejected',
            'confirmed_by' => $admin->id,
            'rejection_reason' => $reason,
        ]);

        Mail::to($payment->student->email)->send(new PaymentRejectedMail($payment));
    }

    /**
     * Record an additional "pay the remaining balance" payment against a
     * single existing active registration (Flow 4 in the spec).
     */
    public function payRemaining(Registration $registration, float $amount, int $paymentMethodId, string $receiptPath, ?string $notes = null): Payment
    {
        return DB::transaction(function () use ($registration, $amount, $paymentMethodId, $receiptPath, $notes) {
            if ($amount <= 0 || $amount > $registration->remaining_amount) {
                throw ValidationException::withMessages(['amount' => 'المبلغ غير صالح بالنسبة للرصيد المتبقي.']);
            }

            $payment = Payment::create([
                'payment_number' => Payment::generateNumber(),
                'student_id' => $registration->student_id,
                'method' => (string) $paymentMethodId,
                'amount' => $amount,
                'receipt_image' => $receiptPath,
                'notes' => $notes,
                'status' => 'pending',
            ]);

            PaymentItem::create([
                'payment_id' => $payment->id,
                'registration_id' => $registration->id,
                'allocated_amount' => $amount,
            ]);

            $this->notifyPaymentSubmitted($payment, $registration->student);

            return $payment;
        });
    }
}
