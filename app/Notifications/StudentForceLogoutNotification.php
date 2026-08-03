<?php

namespace App\Notifications;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Pushed the instant an admin clears a student's locked device (see
 * StudentsController@postClearIp), so an already-open session on the old
 * device is kicked out immediately instead of waiting for that browser to
 * make its next request (which is when EnforceStudentDeviceLock would
 * otherwise catch it). Broadcast-only — no DB row, this is a one-off signal.
 */
class StudentForceLogoutNotification extends Notification implements ShouldBroadcastNow
{
    public int $studentId;

    public function __construct(int $studentId)
    {
        $this->studentId = $studentId;
    }

    public function via(object $notifiable): array
    {
        return ['broadcast'];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'message' => 'تم تسجيل خروجك — تم إلغاء ربط هذا الجهاز من لوحة الإدارة.',
        ]);
    }

    public function broadcastAs(): string
    {
        return 'StudentForceLogoutEvent';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('student-notifications.' . $this->studentId);
    }
}
