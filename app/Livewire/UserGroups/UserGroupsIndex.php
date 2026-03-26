<?php

namespace App\Livewire\UserGroups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserGroup;

class UserGroupsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $groups = UserGroup::query()
            ->withCount(['campaigns', 'users'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->latest()
            ->paginate(15);

        return view('livewire.user-groups.user-groups-index', compact('groups'))
            ->layout('components.layouts.app', ['title' => 'User Groups']);
    }
}
