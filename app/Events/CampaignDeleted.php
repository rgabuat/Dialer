<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class CampaignDeleted implements ShouldBroadcastNow
{
    public function __construct(
        public readonly int $userId,
        public readonly int $campaignId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("App.Models.User.{$this->userId}");
    }

    public function broadcastAs(): string
    {
        return 'CampaignDeleted';
    }

    public function broadcastWith(): array
    {
        return ['campaign_id' => $this->campaignId];
    }
}
