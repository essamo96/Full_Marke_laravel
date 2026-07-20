<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewPaymentSubmittedNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $student = $this->payment->student;
        $studentName = $student ? (app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en) : 'طالب';
        $studentImage = ($student && $student->image) ? (str_starts_with($student->image, 'site/') ? asset($student->image) : asset('storage/' . $student->image)) : asset('assets/admin/media/avatars/blank.png');

        return [
            'payment_id' => $this->payment->id,
            'student_name' => $studentName,
            'student_image' => $studentImage,
            'message' => 'طلب موافقة على دفعة مالية جديدة بقيمة ' . $this->payment->amount . ' JOD',
            'url' => route('approvals.view')
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $student = $this->payment->student;
        $studentName = $student ? (app()->getLocale() == 'ar' ? $student->full_name_ar : $student->full_name_en) : 'طالب';
        $studentImage = ($student && $student->image) ? (str_starts_with($student->image, 'site/') ? asset($student->image) : asset('storage/' . $student->image)) : asset('assets/admin/media/avatars/blank.png');

        return new BroadcastMessage([
            'payment_id' => $this->payment->id,
            'student_name' => $studentName,
            'student_image' => $studentImage,
            'message' => 'طلب موافقة على دفعة مالية جديدة بقيمة ' . $this->payment->amount . ' JOD',
            'url' => route('approvals.view')
        ]);
    }

    /**
     * Get the event name.
     */
    public function broadcastAs()
    {
        return 'NewPaymentEvent';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('admin-notifications');
    }
}
