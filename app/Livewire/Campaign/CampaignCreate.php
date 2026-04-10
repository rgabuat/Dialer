<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;

class CampaignCreate extends Component
{
    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public string $type = 'OUTBOUND';
    public string $dial_mode = 'MANUAL';
    public string $dial_level = '1.00';
    public string $caller_id = '';
    public string $script = '';
    public int $acw_seconds = 0;
    public int $hopper_level = 50;
    public ?int $max_calls = null;

    public function save(): void
    {
        $this->validate([
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'is_active'    => ['boolean'],
            'type'         => ['required', 'in:OUTBOUND,INBOUND,BLENDED'],
            'dial_mode'    => ['required', 'in:MANUAL,PREVIEW,PROGRESSIVE,PREDICTIVE'],
            'dial_level'   => ['numeric', 'min:0.1', 'max:10'],
            'caller_id'    => ['nullable', 'string', 'max:50'],
            'script'       => ['nullable', 'string'],
            'acw_seconds'  => ['integer', 'min:0', 'max:3600'],
            'hopper_level' => ['integer', 'min:1', 'max:1000'],
            'max_calls'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        Campaign::create([
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
            'type'         => $this->type,
            'dial_mode'    => $this->dial_mode,
            'dial_level'   => $this->dial_level,
            'caller_id'    => $this->caller_id ?: null,
            'script'       => $this->script ?: null,
            'acw_seconds'  => $this->acw_seconds,
            'hopper_level' => $this->hopper_level,
            'max_calls'    => $this->max_calls,
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
