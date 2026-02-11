<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesEdit extends Component
{
    public Role $role;
    public array $permissions = [];

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->permissions = $role->permissions->pluck('name')->toArray();
    }

    public function save()
    {
        $this->role->syncPermissions($this->permissions);
        session()->flash('success', 'Role updated');
    }

    public function render()
    {
        return view('livewire.roles.roles-edit', [
            'allPermissions' => Permission::all()->groupBy(
                fn ($p) => explode('.', $p->name)[0]
            ),
        ])->layout('components.layouts.app');
    }
}
