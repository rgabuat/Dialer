<?php

namespace App\Livewire\CallLists;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\CallList;

class CallListCreate extends Component
{
    public Campaign $campaign;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;
    public string $timezone = 'UTC';
    public array $status_filter = ['NEW'];

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function save(): void
    {
        $this->validate([
            'name'          => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['boolean'],
            'sort_order'    => ['integer', 'min:0'],
            'timezone'      => ['required', 'string', 'max:100'],
            'status_filter' => ['array'],
        ]);

        CallList::create([
            'campaign_id'   => $this->campaign->id,
            'name'          => $this->name,
            'description'   => $this->description ?: null,
            'is_active'     => $this->is_active,
            'sort_order'    => $this->sort_order,
            'timezone'      => $this->timezone,
            'status_filter' => $this->status_filter ?: ['NEW'],
        ]);

        session()->flash('success', 'Call list created.');
        $this->redirect(route('campaign.lists', $this->campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.call-lists.call-list-create')
            ->layout('components.layouts.app');
    }
}
