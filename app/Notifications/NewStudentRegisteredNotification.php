<?php

namespace App\Notifications;

use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewStudentRegisteredNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $student;

    /**
     * Create a new notification instance.
     */
    public function __construct(Student $student)
    {
        $this->student = $student;
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
        return [
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name_ar,
            'student_image' => $this->student->image ? asset('storage/' . $this->student->image) : asset('assets/admin/media/avatars/blank.png'),
            'message' => __('app.new_student_registered') . ': ' . $this->student->full_name_ar,
            'url' => route('pending_requests.view')
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'student_id' => $this->student->id,
            'student_name' => $this->student->full_name_ar,
            'student_image' => $this->student->image ? asset('storage/' . $this->student->image) : asset('assets/admin/media/avatars/blank.png'),
            'message' => __('app.new_student_registered') . ': ' . $this->student->full_name_ar,
            'url' => route('pending_requests.view')
        ]);
    }

    /**
     * Get the event name.
     */
    public function broadcastAs()
    {
        return 'NewStudentEvent';
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('admin-notifications');
    }
}
