<?php

namespace App\Livewire\Dispositions;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\Disposition;

class DispositionEdit extends Component
{
    public Campaign $campaign;
    public Disposition $disposition;

    public string $name = '';
    public string $code = '';
    public string $category = 'OTHER';
    public bool $is_dnc = false;
    public bool $requires_callback = false;
    public bool $is_active = true;
    public int $sort_order = 0;

    public function mount(Campaign $campaign, Disposition $disposition): void
    {
        $this->campaign    = $campaign;
        $this->disposition = $disposition;

        $this->name              = $disposition->name;
        $this->code              = $disposition->code;
        $this->category          = $disposition->category;
        $this->is_dnc            = $disposition->is_dnc;
        $this->requires_callback = $disposition->requires_callback;
        $this->is_active         = $disposition->is_active;
        $this->sort_order        = $disposition->sort_order;
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

        $this->disposition->update([
            'name'               => $this->name,
            'code'               => strtoupper($this->code),
            'category'           => $this->category,
            'is_dnc'             => $this->is_dnc,
            'requires_callback'  => $this->requires_callback,
            'is_active'          => $this->is_active,
            'sort_order'         => $this->sort_order,
        ]);

        session()->flash('success', 'Disposition updated.');

        $this->redirect(route('campaign.dispositions', $this->campaign), navigate: true);
    }

    public function render()
    {
        return view('livewire.dispositions.disposition-edit')
            ->layout('components.layouts.app');
    }
}
