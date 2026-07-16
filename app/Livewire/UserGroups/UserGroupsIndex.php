<?php

namespace App\Livewire\UserGroups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\UserGroup;

class UserGroupsIndex extends Component
{
    use WithPagination;

    // ── Listing ────────────────────────────────────────────────────────────
    public string $search  = '';
    public int    $perPage = 15;

    // ── Modal state ────────────────────────────────────────────────────────
    public bool   $showModal        = false;
    public string $modalMode        = 'create'; // 'create' | 'edit'
    public ?int   $editGroupId      = null;
    public bool   $confirmingDelete = false;

    // ── Form fields ────────────────────────────────────────────────────────
    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    // ── Option lists ───────────────────────────────────────────────────────
    public array $campaigns         = [];
    public array $selectedCampaigns = [];

    public function mount(): void
    {
        $this->campaigns = Campaign::orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatedPerPage(): void  { $this->resetPage(); }

    // ── Modal open / close ─────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEdit(int $groupId): void
    {
        $group = UserGroup::findOrFail($groupId);
        $this->editGroupId       = $groupId;
        $this->name              = $group->name;
        $this->description       = $group->description ?? '';
        $this->is_active         = $group->is_active;
        $this->selectedCampaigns = $group->campaigns()->pluck('campaigns.id')->map(fn ($id) => (string) $id)->toArray();
        $this->confirmingDelete  = false;
        $this->modalMode         = 'edit';
        $this->showModal         = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editGroupId      = null;
        $this->name             = '';
        $this->description      = '';
        $this->is_active        = true;
        $this->selectedCampaigns = [];
        $this->confirmingDelete = false;
        $this->resetErrorBag();
    }

    // ── Persist ────────────────────────────────────────────────────────────
    public function save(): void
    {
        if ($this->modalMode === 'create') {
            $this->validate([
                'name'              => ['required', 'string', 'max:255', 'unique:user_groups,name'],
                'description'       => ['nullable', 'string', 'max:1000'],
                'is_active'         => ['boolean'],
                'selectedCampaigns' => ['array'],
                'selectedCampaigns.*' => ['integer', 'exists:campaigns,id'],
            ]);

            $group = UserGroup::create([
                'name'        => $this->name,
                'description' => $this->description,
                'is_active'   => $this->is_active,
            ]);

            $group->campaigns()->sync($this->selectedCampaigns);

            $this->dispatch('toast', message: 'User group created.', type: 'success');
        } else {
            $group = UserGroup::findOrFail($this->editGroupId);

            $this->validate([
                'name'              => ['required', 'string', 'max:255', 'unique:user_groups,name,' . $group->id],
                'description'       => ['nullable', 'string', 'max:1000'],
                'is_active'         => ['boolean'],
                'selectedCampaigns' => ['array'],
                'selectedCampaigns.*' => ['integer', 'exists:campaigns,id'],
            ]);

            $group->update([
                'name'        => $this->name,
                'description' => $this->description,
                'is_active'   => $this->is_active,
            ]);

            $group->campaigns()->sync($this->selectedCampaigns);

            $this->dispatch('toast', message: 'User group updated.', type: 'success');
        }

        $this->closeModal();
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        UserGroup::findOrFail($this->editGroupId)->delete();
        $this->dispatch('toast', message: 'User group deleted.', type: 'success');
        $this->closeModal();
    }

    public function render()
    {
        $groups = UserGroup::query()
            ->withCount(['campaigns', 'users'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.user-groups.user-groups-index', compact('groups'))
            ->layout('components.layouts.admin', ['heading' => 'User Groups']);
    }
}
