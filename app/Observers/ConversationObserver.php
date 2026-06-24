<?php

namespace App\Observers;

use App\Events\CallQueueUpdated;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;

class ConversationObserver
{
    /**
     * Statuses that are relevant to the queue monitor.
     */
    private const QUEUE_STATUSES = ['queued', 'in_progress', 'completed', 'abandoned'];

    public function created(Conversation $conversation): void
    {
        if (in_array($conversation->status, self::QUEUE_STATUSES, true)) {
            $this->broadcast($conversation, 'created');
        }
    }

    public function updated(Conversation $conversation): void
    {
        // Only broadcast if the status changed or it's a queue-relevant status
        if (
            $conversation->isDirty('status') ||
            in_array($conversation->status, self::QUEUE_STATUSES, true)
        ) {
            $this->broadcast($conversation, 'updated');
        }
    }

    public function deleted(Conversation $conversation): void
    {
        $this->broadcast($conversation, 'deleted');
    }

    private function broadcast(Conversation $conversation, string $action): void
    {
        dispatch(function () use ($conversation, $action) {
            try {
                event(new CallQueueUpdated([
                    'action'       => $action,
                    'conversation_id' => $conversation->id,
                    'status'       => $conversation->status,
                    'in_group_id'  => $conversation->in_group_id,
                    'assigned_to'  => $conversation->assigned_to,
                ]));
            } catch (\Throwable $e) {
                Log::warning('[ConversationObserver] Failed to broadcast CallQueueUpdated', [
                    'conversation_id' => $conversation->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }
}
