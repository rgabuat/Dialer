<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;

class CampaignEdit extends Component
{
    public Campaign $campaign;

    public string $name = '';
    public string $phone_number = '';
    public string $description = '';
    public bool $is_active = true;

    public bool $confirmingDelete = false;

    public function mount(Campaign $campaign): void
    {
        $this->campaign     = $campaign;
        $this->name         = $campaign->name;
        $this->phone_number = $campaign->phone_number;
        $this->description  = $campaign->description ?? '';
        $this->is_active    = $campaign->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
        ]);

        $this->campaign->update([
            'name'         => $this->name,
            'phone_number' => $this->phone_number,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
        ]);

        session()->flash('success', 'Campaign updated successfully.');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $this->campaign->delete();

        $this->redirect(route('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaign.campaign-edit')
            ->layout('components.layouts.app');
    }
}
