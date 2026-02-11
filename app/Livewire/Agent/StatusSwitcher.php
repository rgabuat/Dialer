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
        $this->statuses = AgentStatusType::orderBy('name')->get();

        // Current agent status
        $agentStatus = AgentStatus::with('statusType')
            ->where('user_id', auth()->id())
            ->first();

        $this->currentStatus = $agentStatus?->statusType;
        $this->startedAt = $agentStatus?->started_at?->toIso8601String();
    }

    public function setStatus(int $statusId)
    {
        // Change status via service (logs + history + broadcast)
        AgentStatusService::change(auth()->user(), $statusId);

        // Update local UI state
        $this->currentStatus = AgentStatusType::find($statusId);
        $this->startedAt = now()->toIso8601String();

        // Optional: let other components react
        $this->dispatch('agent-status-changed');
    }

    public function render()
    {
        return view('livewire.agent.status-switcher');
    }
}
