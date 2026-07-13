<?php

namespace App\Livewire\Admin;

use App\Models\Campaign;
use App\Models\CidNumber;
use App\Models\Did;
use App\Models\InGroup;
use App\Models\User;
use Livewire\Attributes\On;
use Livewire\Component;

class InGroupModal extends Component
{
    public bool   $open    = false;
    public string $mode    = 'create';
    public ?int   $groupId = null;
    public int    $step    = 1; // 1=Settings 2=DIDs 3=Agents

    // Details
    public string $name          = '';
    public string $description   = '';
    public bool   $is_active     = true;
    public int    $queue_priority = 1;
    public string $agent_routing = 'ring_all';

    // Queue & Drop
    public string $max_wait_seconds       = '';
    public string $queue_max_wait_seconds = '300';
    public string $drop_action            = 'hangup';
    public string $drop_destination       = '';
    public string $web_form_url           = '';

    // Hours
    public bool   $always_open             = true;
    public string $timezone                = 'UTC';
    public string $after_hours_action      = 'hangup';
    public string $after_hours_destination = '';
    public array  $hours = [
        'mon' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
        'tue' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
        'wed' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
        'thu' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
        'fri' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
        'sat' => ['enabled' => false, 'open' => '08:00', 'close' => '17:00'],
        'sun' => ['enabled' => false, 'open' => '08:00', 'close' => '17:00'],
    ];

    // Campaign
    public ?int $campaign_id = null;

    // Agent assignment (edit mode)
    public ?int $addUserId   = null;
    public int  $addPriority = 1;

    // DID assignment (edit mode)
    public ?int $addCidNumberId = null;

    private function defaultHours(): array
    {
        return [
            'mon' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
            'tue' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
            'wed' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
            'thu' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
            'fri' => ['enabled' => true,  'open' => '08:00', 'close' => '17:00'],
            'sat' => ['enabled' => false, 'open' => '08:00', 'close' => '17:00'],
            'sun' => ['enabled' => false, 'open' => '08:00', 'close' => '17:00'],
        ];
    }

    #[On('open-ingroup-create')]
    public function openCreate(): void
    {
        $this->reset(['groupId', 'name', 'description', 'max_wait_seconds',
            'drop_destination', 'web_form_url', 'after_hours_destination', 'campaign_id',
            'addUserId', 'addCidNumberId']);
        $this->resetErrorBag();
        $this->is_active              = true;
        $this->queue_priority         = 1;
        $this->agent_routing          = 'ring_all';
        $this->queue_max_wait_seconds = '300';
        $this->drop_action            = 'hangup';
        $this->always_open            = true;
        $this->timezone               = 'UTC';
        $this->after_hours_action     = 'hangup';
        $this->addPriority            = 1;
        $this->hours                  = $this->defaultHours();
        $this->mode = 'create';
        $this->step = 1;
        $this->open = true;
    }

    #[On('open-ingroup-edit')]
    public function openEdit(int $id): void
    {
        $g = InGroup::findOrFail($id);
        $this->groupId                  = $g->id;
        $this->name                     = $g->name;
        $this->description              = $g->description ?? '';
        $this->is_active                = (bool) $g->is_active;
        $this->queue_priority           = (int) $g->queue_priority;
        $this->agent_routing            = $g->agent_routing;
        $this->max_wait_seconds         = (string) ($g->max_wait_seconds ?? '');
        $this->queue_max_wait_seconds   = (string) ($g->queue_max_wait_seconds ?? '300');
        $this->drop_action              = $g->drop_action;
        $this->drop_destination         = $g->drop_destination ?? '';
        $this->web_form_url             = $g->web_form_url ?? '';
        $this->after_hours_action       = $g->after_hours_action;
        $this->after_hours_destination  = $g->after_hours_destination ?? '';
        $this->timezone                 = $g->timezone ?? 'UTC';
        $this->campaign_id              = $g->campaign_id;
        $this->addUserId                = null;
        $this->addCidNumberId           = null;
        $this->addPriority              = 1;

        if ($g->hours_json) {
            $this->always_open = false;
            $hours = $this->defaultHours();
            foreach ($g->hours_json as $day => $row) {
                $hours[$day] = ['enabled' => !is_null($row), 'open' => $row['open'] ?? '08:00', 'close' => $row['close'] ?? '17:00'];
            }
            $this->hours = $hours;
        } else {
            $this->always_open = true;
            $this->hours = $this->defaultHours();
        }

        $this->resetErrorBag();
        $this->mode = 'edit';
        $this->step = 1;
        $this->open = true;
    }

    public function close(): void { $this->open = false; }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateOnly('name', ['name' => 'required|string|max:255']);
            if ($this->getErrorBag()->has('name')) return;
        }
        if ($this->step < 3) $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function save(): void
    {
        $this->validate([
            'name'                    => 'required|string|max:255',
            'description'             => 'nullable|string',
            'is_active'               => 'boolean',
            'queue_priority'          => 'required|integer|min:1|max:999',
            'agent_routing'           => 'required|in:ring_all,round_robin,fewest_calls,longest_idle',
            'max_wait_seconds'        => 'nullable|integer|min:1|max:3600',
            'queue_max_wait_seconds'  => 'nullable|integer|min:0|max:86400',
            'drop_action'             => 'required|in:hangup,transfer,voicemail',
            'drop_destination'        => 'nullable|string|max:255',
            'web_form_url'            => 'nullable|url|max:1000',
            'after_hours_action'      => 'required|in:hangup,transfer,voicemail',
            'after_hours_destination' => 'nullable|string|max:255',
            'timezone'                => 'required|string|max:100',
            'campaign_id'             => 'nullable|integer|exists:campaigns,id',
        ]);

        $hoursJson = null;
        if (!$this->always_open) {
            $hoursJson = [];
            foreach ($this->hours as $day => $row) {
                $hoursJson[$day] = $row['enabled'] ? ['open' => $row['open'], 'close' => $row['close']] : null;
            }
        }

        $payload = [
            'name'                    => $this->name,
            'description'             => $this->description ?: null,
            'is_active'               => $this->is_active,
            'queue_priority'          => $this->queue_priority,
            'agent_routing'           => $this->agent_routing,
            'max_wait_seconds'        => $this->max_wait_seconds !== '' ? (int) $this->max_wait_seconds : null,
            'queue_max_wait_seconds'  => $this->queue_max_wait_seconds !== '' ? (int) $this->queue_max_wait_seconds : 300,
            'drop_action'             => $this->drop_action,
            'drop_destination'        => $this->drop_destination ?: null,
            'web_form_url'            => $this->web_form_url ?: null,
            'after_hours_action'      => $this->after_hours_action,
            'after_hours_destination' => $this->after_hours_destination ?: null,
            'timezone'                => $this->timezone,
            'hours_json'              => $hoursJson,
            'campaign_id'             => $this->campaign_id,
        ];

        if ($this->mode === 'create') {
            InGroup::create($payload);
        } else {
            InGroup::findOrFail($this->groupId)->update($payload);
        }

        $this->open = false;
        session()->flash('success', $this->mode === 'create' ? 'Inbound group created.' : 'Inbound group updated.');
        $this->dispatch('ingroup-saved');
    }

    // ── Agent management ──────────────────────────────────────────────────────

    public function addAgent(): void
    {
        $this->validate(['addUserId' => 'required|integer|exists:users,id']);
        $group = InGroup::findOrFail($this->groupId);
        $group->users()->syncWithoutDetaching([
            $this->addUserId => ['priority' => $this->addPriority, 'is_active' => true],
        ]);
        $this->addUserId   = null;
        $this->addPriority = 1;
    }

    public function removeAgent(int $userId): void
    {
        InGroup::findOrFail($this->groupId)->users()->detach($userId);
    }

    public function toggleAgent(int $userId, bool $active): void
    {
        InGroup::findOrFail($this->groupId)->users()->updateExistingPivot($userId, ['is_active' => $active]);
    }

    // ── DID management ────────────────────────────────────────────────────────

    public function addDid(): void
    {
        $this->validate(['addCidNumberId' => 'required|integer|exists:cid_numbers,id']);
        $cid = CidNumber::findOrFail($this->addCidNumberId);
        Did::updateOrCreate(
            ['cid_number_id' => $cid->id],
            ['phone_number' => $cid->phone_number, 'in_group_id' => $this->groupId, 'is_active' => true]
        );
        $this->addCidNumberId = null;
    }

    public function removeDid(int $didId): void
    {
        Did::where('id', $didId)->where('in_group_id', $this->groupId)->delete();
    }

    public function toggleDid(int $didId): void
    {
        $did = Did::findOrFail($didId);
        $did->update(['is_active' => !$did->is_active]);
    }

    public function render()
    {
        $group          = $this->groupId ? InGroup::find($this->groupId) : null;
        $assignedAgents = $group ? $group->users()->withPivot(['priority', 'is_active', 'last_call_at'])->orderBy('first_name')->get() : collect();
        $assignedIds    = $assignedAgents->pluck('id');
        $availableUsers = User::whereNotIn('id', $assignedIds)->orderBy('first_name')->get();
        $dids           = $group ? $group->dids()->with('cidNumber')->get() : collect();
        $assignedCidIds = $dids->pluck('cid_number_id');
        $availableCids  = CidNumber::whereNotIn('id', $assignedCidIds)->orderBy('phone_number')->get();

        return view('livewire.admin.in-group-modal', [
            'allCampaigns'  => Campaign::orderBy('name')->get(['id', 'name']),
            'assignedAgents'=> $assignedAgents,
            'availableUsers'=> $availableUsers,
            'dids'          => $dids,
            'availableCids' => $availableCids,
        ]);
    }
}
