<?php

namespace App\Livewire\Agent;

use Livewire\Component;
use App\Models\AgentStatus;
use App\Models\AgentStatusType;
use App\Services\AgentStatusService;
use Illuminate\Support\Facades\Log;

class StatusSwitcher extends Component
{
  public $statuses = [];
  public $currentStatus = null;
  public string $currentStatusName = "";
  public string $currentStatusColor = "";
  public $startedAt;

  public function mount()
  {
    $userId = auth()->id();
    if (!$userId) {
      Log::warning('[StatusSwitcher] mount without authenticated user');
      return;
    }

    // All possible statuses (dropdown options)
    $this->statuses = AgentStatusType::orderBy("name")->get();

    // Current agent status
    $agentStatus = AgentStatus::with("statusType")
      ->where("user_id", $userId)
      ->first();

    $this->currentStatus = $agentStatus?->statusType;
    $this->currentStatusName = $agentStatus?->statusType?->name ?? "Other";
    $this->currentStatusColor = $agentStatus?->statusType?->color ?? "#6b7280";
    $this->startedAt = $agentStatus?->started_at?->toIso8601String();

    Log::info('[StatusSwitcher] mount complete', [
      'user_id' => $userId,
      'statuses_count' => $this->statuses->count(),
      'current_status_type_id' => $agentStatus?->status_type_id,
      'current_status_name' => $this->currentStatusName,
      'started_at' => $this->startedAt,
    ]);
  }

  public function setStatus(int $statusId)
  {
    Log::info('[StatusSwitcher] setStatus requested', [
      'user_id' => auth()->id(),
      'requested_status_type_id' => $statusId,
    ]);

    $statusType = AgentStatusType::find($statusId);

    // Guard: status type must exist (should always be valid from the UI dropdown)
    if (!$statusType) {
      Log::warning('[StatusSwitcher] Ignored unknown status type', [
        'user_id' => auth()->id(),
        'status_type_id' => $statusId,
      ]);
      return;
    }

    $user = auth()->user();
    if (!$user) {
      Log::warning('[StatusSwitcher] setStatus called without authenticated user', [
        'status_type_id' => $statusId,
      ]);
      return;
    }

    try {
      // Change status via service (logs + history + dispatches event after response)
      $startedAt = AgentStatusService::change($user, $statusId);
    } catch (\Throwable $e) {
      Log::error('[StatusSwitcher] Failed to change status', [
        'user_id' => $user->id,
        'status_type_id' => $statusId,
        'error' => $e->getMessage(),
      ]);
      return;
    }

    $now = $startedAt->toIso8601String();

    // Update local UI state
    $this->currentStatus = $statusType;
    $this->currentStatusName = $statusType->name;
    $this->currentStatusColor = $statusType->color;
    $this->startedAt = $now;

    // Let status timer in this component react
    $this->dispatch(
      "agent-status-changed",
      startedAt: $now,
      statusName: $statusType->name,
      statusColor: $statusType->color,
      isAvailable: (bool) $statusType->is_available,
      handles_inbound: (bool) $statusType->handles_inbound,
      handles_outbound: (bool) $statusType->handles_outbound
    );

    // Let the agent status index react (same browser tab, self-update)
    $this->dispatch(
      "agent-row-update",
      user_id: auth()->id(),
      status_name: $statusType->name,
      status_color: $statusType->color,
      is_available: (bool) $statusType->is_available,
      started_at: $now
    );

    Log::info('[StatusSwitcher] setStatus completed', [
      'user_id' => $user->id,
      'status_type_id' => $statusType->id,
      'status_name' => $statusType->name,
      'started_at' => $now,
    ]);
  }

  public function render()
  {
    Log::debug('[StatusSwitcher] render', [
      'user_id' => auth()->id(),
      'current_status_name' => $this->currentStatusName,
      'current_status_color' => $this->currentStatusColor,
      'started_at' => $this->startedAt,
    ]);

    return view("livewire.agent.status-switcher");
  }
}
