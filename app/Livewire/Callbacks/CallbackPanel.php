<?php

namespace App\Livewire\Callbacks;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CallbackSchedule;
use App\Models\Campaign;

class CallbackPanel extends Component
{
    use WithPagination;

    public ?int $campaignId = null;

    public function mount(): void
    {
        $this->campaignId = session('active_campaign_id');
    }

    public function complete(int $id): void
    {
        CallbackSchedule::where('id', $id)->update(['status' => 'completed']);
    }

    public function cancel(int $id): void
    {
        CallbackSchedule::where('id', $id)->update(['status' => 'cancelled']);
    }

    public function render()
    {
        $query = CallbackSchedule::with(['lead', 'campaign', 'assignedAgent'])
            ->where('status', 'pending')
            ->orderBy('scheduled_at');

        if ($this->campaignId) {
            $query->where('campaign_id', $this->campaignId);
        }

        return view('livewire.callbacks.callback-panel', [
            'callbacks' => $query->paginate(20),
        ])->layout('components.layouts.app');
    }
}
