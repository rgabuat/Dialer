<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;

class CampaignCreate extends Component
{
    public string $name = '';
    public string $phone_number = '';
    public string $description = '';
    public bool $is_active = true;

    public function save(): void
    {
        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:50'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
        ]);

        Campaign::create([
            'name'         => $this->name,
            'phone_number' => $this->phone_number,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
        ]);

        session()->flash('success', 'Campaign created successfully.');

        $this->redirect(route('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaign.campaign-create')
            ->layout('components.layouts.app');
    }
}
