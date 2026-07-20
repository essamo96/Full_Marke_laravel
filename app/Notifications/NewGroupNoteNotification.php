<?php

namespace App\Notifications;

use App\Models\GroupNote;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewGroupNoteNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $note;
    public $studentId;

    public function __construct(GroupNote $note, $studentId)
    {
        $this->note = $note;
        $this->studentId = $studentId;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'group_id' => $this->note->group_id,
            'message' => 'ملاحظة جديدة في مجموعتك: ' . $this->note->title,
            'url' => route('student.groups.show', $this->note->group),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function broadcastAs()
    {
        return 'NewGroupNoteEvent';
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('student-notifications.' . $this->studentId);
    }
}
