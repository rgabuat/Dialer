<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallQueueUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $payload)
    {
        //
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('call-queue');
    }

    public function broadcastAs(): string
    {
        return 'CallQueueUpdated';
    }

    public function broadcastWith(): array
    {
        return $this->payload;
    }
}
