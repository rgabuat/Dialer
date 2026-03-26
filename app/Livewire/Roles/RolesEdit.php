<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesEdit extends Component
{
    public Role $role;
    public string $name = '';
    public array $permissions = [];
    public string $newPermission = '';

    protected function rules(): array
    {
        return [
            'name'          => 'required|string|unique:roles,name,' . $this->role->id,
            'permissions'   => 'array',
            'newPermission' => 'nullable|string|regex:/^[a-z0-9_]+\.[a-z0-9_]+$/i',
        ];
    }

    public function mount(Role $role)
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->permissions = $role->permissions->pluck('name')->toArray();
    }

    public function save()
    {
        $this->validateOnly('name');
        $this->validateOnly('permissions');

        $this->role->update(['name' => $this->name]);
        $this->role->syncPermissions($this->permissions);

        session()->flash('success', 'Role updated successfully.');
    }

    public function addPermission()
    {
        $this->validateOnly('newPermission');

        $permission = Permission::firstOrCreate(
            ['name' => $this->newPermission, 'guard_name' => 'web']
        );

        if (! in_array($permission->name, $this->permissions)) {
            $this->permissions[] = $permission->name;
        }

        $this->newPermission = '';
    }

    public function deletePermission(int $id)
    {
        $permission = Permission::findById($id);

        // Remove from current selection
        $this->permissions = array_values(
            array_filter($this->permissions, fn ($p) => $p !== $permission->name)
        );

        // Detach from all roles then delete
        $permission->roles()->detach();
        $permission->delete();
    }

    public function render()
    {
        return view('livewire.roles.roles-edit', [
            'groupedPermissions' => Permission::all()->groupBy(
                fn ($p) => explode('.', $p->name)[0]
            ),
        ])->layout('components.layouts.app');
    }
}
