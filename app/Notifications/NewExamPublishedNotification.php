<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewExamPublishedNotification extends Notification implements ShouldBroadcastNow
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
        // ShouldBroadcastNow on the notification itself does not make Laravel push it
        // immediately (the underlying BroadcastNotificationCreated event is queued), so
        // the "live" alert would silently wait for a queue worker. Force the sync
        // connection so it reaches the student's open page right away.
        return (new BroadcastMessage($this->toArray($notifiable)))->onConnection('sync');
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
