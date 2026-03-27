<?php

namespace App\Livewire\Agent;

use Livewire\Component;
use App\Models\AgentStatus;
use App\Models\AgentStatusType;

class AgentStatusIndex extends Component
{
    public string $search = '';
    public string $filterStatus = '';

    public function render()
    {
        $statuses = AgentStatus::with(['user.userGroup', 'statusType'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn ($u) =>
                    $u->where('first_name', 'like', "%{$this->search}%")
                      ->orWhere('last_name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                );
            })
            ->when($this->filterStatus, fn ($q) =>
                $q->whereHas('statusType', fn ($s) =>
                    $s->where('name', $this->filterStatus)
                )
            )
            ->get();

        $groups = $statuses
            ->groupBy(fn ($s) => $s->user->userGroup?->name ?? 'Unassigned')
            ->sortKeys();

        return view('livewire.agent.agent-status-index', [
            'groups'      => $groups,
            'statusTypes' => AgentStatusType::orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
