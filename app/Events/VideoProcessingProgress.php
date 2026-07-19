<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VideoProcessingProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $resourceId;
    public $percentage;

    public function __construct(int $resourceId, int $percentage)
    {
        $this->resourceId = $resourceId;
        $this->percentage = $percentage;
    }

    public function broadcastOn(): array
    {
        // Broadcast on a public channel since it's just progress for admin UI.
        // If we want private, we could use PrivateChannel('admin') 
        return [
            new Channel('system-jobs'),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'video.processing';
    }
}
