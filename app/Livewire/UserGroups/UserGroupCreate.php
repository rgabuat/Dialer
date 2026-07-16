<?php

namespace App\Livewire\UserGroups;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\UserGroup;

class UserGroupCreate extends Component
{
    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    public array $campaigns         = [];
    public array $selectedCampaigns = [];

    public function mount(): void
    {
        $this->campaigns = Campaign::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();
    }

    public function save(): void
    {
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

        $this->redirectRoute('user-groups.index');
    }

    public function render()
    {
        return view('livewire.user-groups.user-group-create')
            ->layout('components.layouts.admin', ['heading' => 'New User Group']);
    }
}
