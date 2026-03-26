<?php

namespace App\Livewire\UserGroups;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\UserGroup;

class UserGroupEdit extends Component
{
    public UserGroup $group;

    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    public array $campaigns         = [];
    public array $selectedCampaigns = [];

    public bool $confirmingDelete = false;

    public function mount(UserGroup $group): void
    {
        $this->group       = $group;
        $this->name        = $group->name;
        $this->description = $group->description ?? '';
        $this->is_active   = $group->is_active;

        $this->campaigns         = Campaign::orderBy('name')->get(['id', 'name'])->toArray();
        $this->selectedCampaigns = $group->campaigns()->pluck('campaigns.id')->map(fn ($id) => (string) $id)->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'name'              => ['required', 'string', 'max:255', 'unique:user_groups,name,' . $this->group->id],
            'description'       => ['nullable', 'string', 'max:1000'],
            'is_active'         => ['boolean'],
            'selectedCampaigns' => ['array'],
            'selectedCampaigns.*' => ['integer', 'exists:campaigns,id'],
        ]);

        $this->group->update([
            'name'        => $this->name,
            'description' => $this->description,
            'is_active'   => $this->is_active,
        ]);

        $this->group->campaigns()->sync($this->selectedCampaigns);

        $this->dispatch('toast', message: 'User group updated.', type: 'success');

        $this->redirectRoute('user-groups.index');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $this->group->delete();

        $this->dispatch('toast', message: 'User group deleted.', type: 'success');

        $this->redirectRoute('user-groups.index');
    }

    public function render()
    {
        return view('livewire.user-groups.user-group-edit')
            ->layout('components.layouts.app', ['title' => 'Edit User Group']);
    }
}
