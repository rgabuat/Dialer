<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Campaign;

class CampaignsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $campaigns = Campaign::when($this->search, fn ($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('phone_number', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate(20);

        return view('livewire.campaign.campaigns-index', compact('campaigns'))
            ->layout('components.layouts.app');
    }
}
