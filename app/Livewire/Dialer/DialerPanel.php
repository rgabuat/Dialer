<?php

namespace App\Livewire\Dialer;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\DialerHopper;
use App\Services\HopperService;

class DialerPanel extends Component
{
    public ?int $campaignId = null;
    public ?Campaign $campaign = null;

    // Current dial state
    public bool $isDialing = false;
    public ?string $currentPhone = null;
    public ?int $currentLeadId = null;
    public ?string $currentCallSid = null;
    public ?int $hopperCount = null;

    protected HopperService $hopper;

    public function boot(HopperService $hopper): void
    {
        $this->hopper = $hopper;
    }

    public function mount(): void
    {
        $this->campaignId = session('active_campaign_id');
        if ($this->campaignId) {
            $this->campaign = Campaign::find($this->campaignId);
            $this->refreshHopperCount();
        }
    }

    /**
     * Called when agent status changes (Livewire event from StatusSwitcher).
     */
    public function handleStatusChange(array $payload): void
    {
        $this->campaignId = session('active_campaign_id');
        $this->campaign   = $this->campaignId ? Campaign::find($this->campaignId) : null;
        $this->refreshHopperCount();
    }

    /**
     * Fill the hopper on demand (for PROGRESSIVE/PREDICTIVE).
     */
    public function fillHopper(): void
    {
        if (!$this->campaign) {
            return;
        }
        $added = $this->hopper->fill($this->campaign);
        $this->refreshHopperCount();
        session()->flash('hopper_msg', "Added {$added} leads to hopper.");
    }

    /**
     * Preview dial — shows lead info before actually connecting.
     */
    public function previewNext(): void
    {
        if (!$this->campaign) {
            return;
        }

        $entry = $this->hopper->nextLead($this->campaign);
        if (!$entry) {
            $this->dispatch('dialer-no-leads');
            return;
        }

        $this->currentPhone  = $entry->phone_number;
        $this->currentLeadId = $entry->lead_id;
        $this->dispatch('dialer-preview', phone: $entry->phone_number, leadId: $entry->lead_id, hopperId: $entry->id);
    }

    /**
     * Refresh hopper count.
     */
    public function refreshHopperCount(): void
    {
        if (!$this->campaignId) {
            $this->hopperCount = null;
            return;
        }
        $this->hopperCount = DialerHopper::where('campaign_id', $this->campaignId)
            ->where('status', 'pending')
            ->count();
    }

    public function render()
    {
        if ($this->campaign) {
            $this->campaign->refresh();
        }

        return view('livewire.dialer.dialer-panel');
    }
}
