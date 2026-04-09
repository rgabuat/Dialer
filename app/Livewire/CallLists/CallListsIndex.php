<?php

namespace App\Livewire\CallLists;

use Livewire\Component;
use App\Models\Campaign;

class CallListsIndex extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function render()
    {
        $lists = $this->campaign->callLists()
            ->withCount('leads')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('livewire.call-lists.call-lists-index', compact('lists'))
            ->layout('components.layouts.app');
    }
}
