<?php

namespace App\Livewire\Dispositions;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\Disposition;

class DispositionCreate extends Component
{
    public Campaign $campaign;

    public string $name = '';
    public string $code = '';
    public string $category = 'OTHER';
    public bool $is_dnc = false;
    public bool $requires_callback = false;
    public bool $is_active = true;
    public int $sort_order = 0;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function save(): void
    {
        $this->validate([
            'name'               => 'required|string|max:100',
            'code'               => 'required|string|max:20',
            'category'           => 'required|in:SALE,DNC,CALLBACK,RETRY,OTHER',
            'is_dnc'             => 'boolean',
            'requires_callback'  => 'boolean',
            'is_active'          => 'boolean',
            'sort_order'         => 'integer|min:0',
        ]);

        Disposition::create([
            'campaign_id'        => $this->campaign->id,
            'name'               => $this->name,
            'code'               => strtoupper($this->code),
            'category'           => $this->category,
            'is_dnc'             => $this->is_dnc,
            'requires_callback'  => $this->requires_callback,
            'is_active'          => $this->is_active,
            'sort_order'         => $this->sort_order,
        ]);

        session()->flash('success', 'Disposition created.');

        $this->redirect(route('campaign.dispositions', $this->campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.dispositions.disposition-create')
            ->layout('components.layouts.app');
    }
}
