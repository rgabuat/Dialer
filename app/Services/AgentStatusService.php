<?php

namespace App\Services;

use App\Models\User;
use App\Models\AgentStatus;
use App\Models\AgentStatusLog;
use App\Models\AgentStatusType;
use App\Events\AgentStatusUpdated;
use Illuminate\Support\Facades\Log;

class AgentStatusService
{
  public static function change(User $user, int $statusTypeId): \Carbon\Carbon
  {
    Log::info('[AgentStatusService] change() called', [
      'user_id'        => $user->id,
      'user_email'     => $user->email,
      'status_type_id' => $statusTypeId,
      'trace'          => collect(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5))
                            ->map(fn($f) => ($f['class'] ?? '') . '::' . ($f['function'] ?? '') . ' L' . ($f['line'] ?? '?'))
                            ->implode(' → '),
    ]);

    $now = now();

    $current = AgentStatus::where("user_id", $user->id)->first();

    if ($current) {
      Log::debug('[AgentStatusService] Updating existing AgentStatus row', [
        'user_id'         => $user->id,
        'old_status_type' => $current->status_type_id,
        'new_status_type' => $statusTypeId,
        'old_started_at'  => (string) $current->started_at,
      ]);
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
      Log::debug('[AgentStatusService] Creating new AgentStatus row', [
        'user_id'        => $user->id,
        'status_type_id' => $statusTypeId,
      ]);
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

    if (!$statusType) {
      Log::error('[AgentStatusService] AgentStatusType not found', ['status_type_id' => $statusTypeId, 'user_id' => $user->id]);
      return $now;
    }

    Log::info('[AgentStatusService] Status changed successfully, dispatching event after response', [
      'user_id'          => $user->id,
      'status_type_id'   => $statusTypeId,
      'status_name'      => $statusType->name,
      'handles_inbound'  => (bool) $statusType->handles_inbound,
      'handles_outbound' => (bool) $statusType->handles_outbound,
      'is_available'     => (bool) $statusType->is_available,
    ]);

    $payload = [
      "user_id" => $user->id,
      "user_name" => $user->first_name . " " . $user->last_name,
      "user_email" => $user->email,
      "user_group_id" => $user->user_group_id,
      "user_group_name" => $user->userGroup?->name ?? "Unassigned",
      "status_name" => $statusType->name,
      "status_color" => $statusType->color,
      "is_available" => (bool) $statusType->is_available,
      "handles_inbound" => (bool) $statusType->handles_inbound,
      "handles_outbound" => (bool) $statusType->handles_outbound,
      "started_at" => $now->toIso8601String(),
    ];

    // Dispatch after response to avoid blocking Livewire status-change request.
    dispatch(function () use ($payload) {
      try {
        Log::debug('[AgentStatusService] afterResponse: firing AgentStatusUpdated event', [
          'user_id'        => $payload['user_id'] ?? null,
          'status_name'    => $payload['status_name'] ?? null,
          'handles_inbound'=> $payload['handles_inbound'] ?? null,
        ]);
        event(new AgentStatusUpdated($payload));
      } catch (\Throwable $e) {
        Log::warning('[AgentStatusService] Failed to dispatch AgentStatusUpdated', [
          'user_id' => $payload['user_id'] ?? null,
          'error'   => $e->getMessage(),
          'file'    => $e->getFile() . ':' . $e->getLine(),
        ]);
      }
    })->afterResponse();

    return $now;
  }
}
