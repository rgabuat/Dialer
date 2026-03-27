<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\AgentStatusType;
use App\Services\AgentStatusService;

class CampaignSelect extends Component
{
    public $campaignId = null;

    public function mount()
    {
        $this->campaignId = session('active_campaign_id');
    }

    public function select(): void
    {
        $this->validate([
            'campaignId' => ['required', 'integer'],
        ]);

        $user  = auth()->user();
        $group = $user->userGroup;

        abort_unless($group, 403, 'No user group assigned.');

        $campaign = $group->campaigns()
            ->where('is_active', true)
            ->findOrFail($this->campaignId);

        session(['active_campaign_id' => $campaign->id]);

        $otherStatus = AgentStatusType::where('slug', 'other')->first();
        if ($otherStatus) {
            AgentStatusService::change(auth()->user(), $otherStatus->id);
        }

        $this->redirect(route('dashboard'), navigate: true);
    }

    public function render()
{
        $user  = auth()->user();
        $group = $user->userGroup;

        $campaigns = $group
            ? $group->campaigns()->where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('livewire.campaign.campaign-select', compact('campaigns'))
            ->layout('components.layouts.guest', ['title' => 'Select Campaign']);
    }
}
