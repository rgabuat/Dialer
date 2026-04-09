<?php

namespace App\Livewire\Dispositions;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\Disposition;

class DispositionsIndex extends Component
{
    public Campaign $campaign;

    public function mount(Campaign $campaign): void
    {
        $this->campaign = $campaign;
    }

    public function delete(int $id): void
    {
        $disp = Disposition::where('id', $id)
            ->where('campaign_id', $this->campaign->id)
            ->firstOrFail();

        $disp->delete();

        session()->flash('success', 'Disposition deleted.');
    }

    public function render()
    {
        $dispositions = Disposition::where(function ($q) {
            $q->whereNull('campaign_id')
              ->orWhere('campaign_id', $this->campaign->id);
        })
            ->orderBy('campaign_id')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.dispositions.dispositions-index', [
            'dispositions' => $dispositions,
        ])->layout('components.layouts.app');
    }
}
