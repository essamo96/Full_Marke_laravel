<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentNewPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $newPassword)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('app.new_password_sent'))
            ->greeting(__('app.forgot_password'))
            ->line(__('app.forgot_password_instructions'))
            ->line(__('app.password') . ': ' . $this->newPassword)
            ->line(__('app.back_to_login'))
            ->action(__('app.back_to_login'), route('student.login'));
    }
}
