<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use App\Services\PermissionRegistrar;
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

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
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

    public function toggleModule(array $perms): void
    {
        $allChecked = count(array_diff($perms, $this->permissions)) === 0;

        if ($allChecked) {
            $this->permissions = array_values(array_diff($this->permissions, $perms));
        } else {
            $this->permissions = array_values(
                array_unique(array_merge($this->permissions, $perms))
            );
        }
    }

    public function toggleAll(array $allPerms): void
    {
        if (count(array_diff($allPerms, $this->permissions)) === 0) {
            $this->permissions = [];
        } else {
            $this->permissions = array_values(
                array_unique(array_merge($this->permissions, $allPerms))
            );
        }
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
            'pageGroups'         => PermissionRegistrar::$pageGroups,
            'crudGroups'         => PermissionRegistrar::$crudGroups,
            'groupedPermissions' => Permission::where('name', 'not like', 'page.%')
                ->get()
                ->groupBy(fn ($p) => explode('.', $p->name)[0])
                ->toBase(),
        ])->layout('components.layouts.admin', ['heading' => 'Edit Role']);
    }
}
