<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VisitEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $ip;
    public $userAgent;
    public $path;
    public $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(string $ip, string $userAgent, string $path, ?int $userId = null)
    {
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->path = $path;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
