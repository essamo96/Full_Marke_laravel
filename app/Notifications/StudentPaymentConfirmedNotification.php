<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class StudentPaymentConfirmedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'message' => 'تم تأكيد دفعتك المالية بقيمة ' . $this->payment->amount . ' JOD بنجاح. شكراً لك!',
            'url' => route('student.dashboard')
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'payment_id' => $this->payment->id,
            'message' => 'تم تأكيد دفعتك المالية بقيمة ' . $this->payment->amount . ' JOD بنجاح. شكراً لك!',
            'url' => route('student.dashboard')
        ]);
    }

    public function broadcastAs()
    {
        return 'StudentPaymentConfirmedEvent';
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('student-notifications.' . $this->payment->student_id);
    }
}
