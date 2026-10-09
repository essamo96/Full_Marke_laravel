<?php

namespace App\Notifications;

use App\Models\Exam;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExamStartingNowNotification extends Notification implements ShouldBroadcastNow
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
        return [
            'exam_id' => $this->exam->id,
            'message' => 'حان الآن موعد امتحان: ' . $this->exam->title . ' — يمكنك البدء الآن.',
            'url' => route('student.exams.index'),
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
        return 'ExamStartingNowEvent';
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('student-notifications.' . $this->studentId);
    }
}
