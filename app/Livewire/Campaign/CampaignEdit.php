<?php

namespace App\Livewire\Campaign;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\InGroup;
use App\Models\User;
use App\Events\CampaignDeleted;

class CampaignEdit extends Component
{
    public Campaign $campaign;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public string $type = 'OUTBOUND';
    public string $dial_mode = 'MANUAL';
    public string $dial_level = '1.00';
    public string $caller_id = '';
    public bool $cid_rotation = false;
    public string $script = '';
    public int $acw_seconds = 0;
    public int $hopper_level = 50;
    public ?int $max_calls = null;

    /** @var array<int, string> IDs of in-groups currently assigned to this campaign */
    public array $selectedInGroupIds = [];

    public bool $confirmingDelete = false;

    public function mount(Campaign $campaign): void
    {
        $this->campaign     = $campaign;
        $this->name         = $campaign->name;
        $this->description  = $campaign->description ?? '';
        $this->is_active    = $campaign->is_active;
        $this->type         = $campaign->type ?? 'OUTBOUND';
        $this->dial_mode    = $campaign->dial_mode ?? 'MANUAL';
        $this->dial_level   = (string) ($campaign->dial_level ?? '1.00');
        $this->caller_id    = $campaign->caller_id ?? '';
        $this->cid_rotation = (bool) ($campaign->cid_rotation ?? false);
        $this->script       = $campaign->script ?? '';
        $this->acw_seconds  = (int) ($campaign->acw_seconds ?? 0);
        $this->hopper_level = (int) ($campaign->hopper_level ?? 50);
        $this->max_calls    = $campaign->max_calls ? (int) $campaign->max_calls : null;

        // Load currently assigned in-groups (cast to string so wire:model checkboxes work)
        $this->selectedInGroupIds = $campaign->inGroups()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

    }

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
            'cid_rotation' => ['boolean'],
            'script'       => ['nullable', 'string'],
            'acw_seconds'  => ['integer', 'min:0', 'max:3600'],
            'hopper_level' => ['integer', 'min:1', 'max:1000'],
            'max_calls'    => ['nullable', 'integer', 'min:1', 'max:100'],
            'selectedInGroupIds'   => ['array'],
            'selectedInGroupIds.*' => ['integer', 'exists:in_groups,id'],
        ]);

        $this->campaign->update([
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
            'type'         => $this->type,
            'dial_mode'    => $this->dial_mode,
            'dial_level'   => $this->dial_level,
            'caller_id'    => $this->caller_id ?: null,
            'cid_rotation' => $this->cid_rotation,
            'script'       => $this->script ?: null,
            'acw_seconds'  => $this->acw_seconds,
            'hopper_level' => $this->hopper_level,
            'max_calls'    => $this->max_calls,
        ]);

        // Sync in-group assignments via campaign_id FK
        $selectedIds = array_map('intval', $this->selectedInGroupIds);
        InGroup::whereIn('id', $selectedIds)
            ->update(['campaign_id' => $this->campaign->id]);
        InGroup::where('campaign_id', $this->campaign->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['campaign_id' => null]);

        session()->flash('success', 'Campaign updated successfully.');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $campaignId = $this->campaign->id;

        // Collect all users in the campaign's user groups before deleting
        $groupIds = $this->campaign->userGroups()->pluck('id');
        $userIds  = User::whereIn('user_group_id', $groupIds)->pluck('id');

        $this->campaign->delete();

        // Notify each affected user — they will be logged out if this was their active campaign
        foreach ($userIds as $userId) {
            broadcast(new CampaignDeleted($userId, $campaignId));
        }

        $this->redirect(route('campaigns.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.campaign.campaign-edit', [
            'allInGroups' => InGroup::orderBy('name')->get(),
        ])->layout('components.layouts.app');
    }
}
