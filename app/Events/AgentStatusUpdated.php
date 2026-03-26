<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $payload)
    {
        //
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('agent-status');
    }

    public function broadcastAs(): string
    {
        return 'AgentStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
