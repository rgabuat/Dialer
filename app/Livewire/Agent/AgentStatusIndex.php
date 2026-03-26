<?php

namespace App\Livewire\Agent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AgentStatus;
use App\Models\AgentStatusType;
use App\Models\User;

class AgentStatusIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = AgentStatus::with(['user', 'statusType'])
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
            ->latest('created_at');

        return view('livewire.agent.agent-status-index', [
            'statuses'     => $query->paginate(20),
            'statusTypes'  => AgentStatusType::orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
