<?php

namespace App\Services;

use App\Models\User;
use App\Models\AgentStatus;
use App\Models\AgentStatusLog;

class AgentStatusService
{
    public static function change(User $user, int $statusTypeId): void
    {
        $now = now();

        $current = AgentStatus::where('user_id', $user->id)->first();

        if ($current) {
            AgentStatusLog::where('user_id', $user->id)
                ->whereNull('ended_at')
                ->update([
                    'ended_at' => $now,
                    'duration_seconds' =>
                        $now->diffInSeconds($current->started_at),
                ]);

            $current->update([
                'status_type_id' => $statusTypeId,
                'started_at' => $now,
            ]);
        } else {
            AgentStatus::create([
                'user_id' => $user->id,
                'status_type_id' => $statusTypeId,
                'started_at' => $now,
            ]);
        }

        AgentStatusLog::create([
            'user_id' => $user->id,
            'status_type_id' => $statusTypeId,
            'started_at' => $now,
        ]);
    }
}
