<?php

namespace App\Notifications;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewContactMessageNotification extends Notification implements ShouldBroadcastNow
{
    use Queueable;

    public $contact;

    public function __construct(Contact $contact)
    {
        $this->contact = $contact;
    }

    public function via(object $notifiable): array
    {
        return ['broadcast']; // you can add 'database' if you want it saved in db
    }

    public function toArray(object $notifiable): array
    {
        return [
            'contact_id' => $this->contact->id,
            'name' => $this->contact->name,
            'message' => 'New Contact Message: ' . $this->contact->name,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'contact_id' => $this->contact->id,
            'name' => $this->contact->name,
            'message' => 'New Contact Message: ' . $this->contact->name,
        ]);
    }

    public function broadcastAs()
    {
        return 'NewContactEvent';
    }

    public function broadcastOn()
    {
        return new \Illuminate\Broadcasting\Channel('admin-notifications');
    }
}
