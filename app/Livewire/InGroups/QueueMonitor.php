<?php

namespace App\Livewire\InGroups;

use App\Models\Campaign;
use App\Models\Conversation;
use App\Models\ConversationNote;
use App\Models\InGroup;
use Livewire\Component;

class QueueMonitor extends Component
{
    public ?int $selectedCampaign = null;

    public function mount(): void
    {
        // Default to the user's active campaign session
        $this->selectedCampaign = (int) session('active_campaign_id') ?: null;
    }

    public function render()
    {
        $user = auth()->user();

        // Scope available campaigns to the user's user group (Super Admin sees all)
        $campaignQuery = Campaign::where('is_active', true)->orderBy('name');
        if (!$user->hasRole('Super Admin') && $user->userGroup) {
            $groupCampaignIds = $user->userGroup->campaigns()->pluck('campaigns.id');
            $campaignQuery->whereIn('id', $groupCampaignIds);
        }
        $campaigns = $campaignQuery->get();

        // Ensure selectedCampaign is within accessible campaigns
        if ($this->selectedCampaign && !$campaigns->contains('id', $this->selectedCampaign)) {
            $this->selectedCampaign = $campaigns->first()?->id;
        }

        // In-groups for the selected campaign
        $inGroups = InGroup::query()
            ->where('is_active', true)
            ->when($this->selectedCampaign, fn($q) => $q->where('campaign_id', $this->selectedCampaign))
            ->orderByDesc('queue_priority')
            ->get();

        $inGroupIds = $inGroups->pluck('id');

        // All live calls within these in-groups
        $liveCalls = Conversation::with(['inGroup', 'assignedAgent', 'lead'])
            ->whereIn('status', ['queued', 'in_progress'])
            ->whereIn('in_group_id', $inGroupIds)
            ->orderBy('started_at')
            ->get();

        // Latest note from any prior conversation per lead
        $leadIds = $liveCalls->pluck('lead_id')->filter()->unique()->values();
        $leadNotes = ConversationNote::with('author')
            ->whereHas('conversation', fn($q) => $q->whereIn('lead_id', $leadIds))
            ->latest()
            ->get()
            ->groupBy(fn($note) => $note->conversation->lead_id ?? null)
            ->map(fn($notes) => $notes->first());

        $totalQueued = $liveCalls->where('status', 'queued')->count();
        $totalActive = $liveCalls->where('status', 'in_progress')->count();
        $longestWait = $liveCalls->where('status', 'queued')->min('started_at');
        $longestSecs = $longestWait ? now()->diffInSeconds($longestWait) : 0;

        return view('livewire.in-groups.queue-monitor', [
            'campaigns'    => $campaigns,
            'inGroups'     => $inGroups,
            'liveCalls'    => $liveCalls,
            'leadNotes'    => $leadNotes,
            'totalQueued'  => $totalQueued,
            'totalActive'  => $totalActive,
            'longestSecs'  => $longestSecs,
        ])->layout('components.layouts.app');
    }
}
