<?php

namespace App\Livewire\Agent;

use Livewire\Component;
use App\Models\AgentStatus;
use App\Models\AgentStatusType;
use App\Services\AgentStatusService;

class StatusSwitcher extends Component
{
  public $statuses = [];
  public $currentStatus = null;
  public $startedAt;

  public function mount()
  {
    // All possible statuses (dropdown options)
    $this->statuses = AgentStatusType::orderBy("name")->get();

    // Current agent status
    $agentStatus = AgentStatus::with("statusType")
      ->where("user_id", auth()->id())
      ->first();

    $this->currentStatus = $agentStatus?->statusType;
    $this->startedAt = $agentStatus?->started_at?->toIso8601String();
  }

  public function setStatus(int $statusId)
  {
    // Change status via service (logs + history + broadcast)
    $startedAt = AgentStatusService::change(auth()->user(), $statusId);

    $statusType = AgentStatusType::find($statusId);
    $now = $startedAt->toIso8601String();

    // Update local UI state
    $this->currentStatus = $statusType;
    $this->startedAt = $now;

    // Let status timer in this component react
    $this->dispatch(
      "agent-status-changed",
      startedAt: $now,
      statusName: $statusType->name,
      statusColor: $statusType->color,
      isAvailable: (bool) $statusType->is_available
    );

    // Let the agent status index react (same browser tab, self-update)
    $this->dispatch("agent-row-update", [
      "user_id" => auth()->id(),
      "status_name" => $statusType->name,
      "status_color" => $statusType->color,
      "is_available" => (bool) $statusType->is_available,
      "started_at" => $now,
    ]);
  }

  public function render()
  {
    return view("livewire.agent.status-switcher");
  }
}
