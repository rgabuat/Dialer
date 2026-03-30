<?php

namespace App\Services;

use App\Models\User;
use App\Models\AgentStatus;
use App\Models\AgentStatusLog;
use App\Models\AgentStatusType;
use App\Events\AgentStatusUpdated;

class AgentStatusService
{
  public static function change(User $user, int $statusTypeId): \Carbon\Carbon
  {
    $now = now();

    $current = AgentStatus::where("user_id", $user->id)->first();

    if ($current) {
      AgentStatusLog::where("user_id", $user->id)
        ->whereNull("ended_at")
        ->update([
          "ended_at" => $now,
          "duration_seconds" => $now->diffInSeconds($current->started_at),
        ]);

      $current->update([
        "status_type_id" => $statusTypeId,
        "started_at" => $now,
      ]);
    } else {
      AgentStatus::create([
        "user_id" => $user->id,
        "status_type_id" => $statusTypeId,
        "started_at" => $now,
      ]);
    }

    AgentStatusLog::create([
      "user_id" => $user->id,
      "status_type_id" => $statusTypeId,
      "started_at" => $now,
    ]);

    $statusType = AgentStatusType::find($statusTypeId);

    $payload = [
      "user_id" => $user->id,
      "user_name" => $user->first_name . " " . $user->last_name,
      "user_email" => $user->email,
      "user_group_id" => $user->user_group_id,
      "user_group_name" => $user->userGroup?->name ?? "Unassigned",
      "status_name" => $statusType->name,
      "status_color" => $statusType->color,
      "is_available" => (bool) $statusType->is_available,
      "started_at" => $now->toIso8601String(),
    ];

    // Broadcast after the response is sent so it doesn't block the UI update
    app()->terminating(fn() => broadcast(new AgentStatusUpdated($payload)));

    return $now;
  }
}
