<?php

namespace App\Livewire\Roles;

use Livewire\Component;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesCreate extends Component
{
    public string $name = '';
    public array $permissions = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array',
        ];
    }

    public function save()
    {
        $this->validate();

        $role = Role::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($this->permissions);

        session()->flash('success', 'Role created successfully.');

        return redirect()->route('roles.index');
    }

    public function render()
    {
        return view('livewire.roles.roles-create', [
            'groupedPermissions' => Permission::all()->groupBy(
                fn ($p) => explode('.', $p->name)[0]
            ),
        ])->layout('components.layouts.app');
    }
}
