<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;

class RolesIndex extends Component
{
    public function delete(Role $role)
    {
        $role->delete();
    }

    public function render()
    {
        return view('livewire.roles.roles-index', [
            'roles' => Role::withCount('permissions')->get(),
        ])->layout('components.layouts.app');
    }
}
