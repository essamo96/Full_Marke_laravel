<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewExamPublishedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $exam;
    public $studentId;

    public function __construct(Exam $exam, $studentId)
    {
        $this->exam = $exam;
        $this->studentId = $studentId;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        $timeStr = $this->exam->start_time ? $this->exam->start_time->format('Y-m-d H:i') : 'مفتوح';
        $durationStr = $this->exam->duration_minutes ? $this->exam->duration_minutes . ' دقيقة' : 'غير محدد';
        return [
            'exam_id' => $this->exam->id,
            'message' => 'تم نشر امتحان جديد: ' . $this->exam->title . ' | موعد البدء: ' . $timeStr . ' | المدة: ' . $durationStr,
            'url' => route('student.exams.index')
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs()
    {
        return 'NewExamPublishedEvent';
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('student-notifications.' . $this->studentId);
    }
}
