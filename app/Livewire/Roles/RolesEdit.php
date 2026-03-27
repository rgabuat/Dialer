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
            'newPermission' => 'nullable|string|regex:/^[a-z0-9_]+(\.[a-z0-9_]+)+$/i',
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

    public function saveName(): void
    {
        $this->validateOnly('name');
        $this->role->update(['name' => $this->name]);
    }

    public function togglePermission(string $permissionName): void
    {
        if (in_array($permissionName, $this->permissions)) {
            $this->permissions = array_values(
                array_filter($this->permissions, fn ($p) => $p !== $permissionName)
            );
        } else {
            $this->permissions[] = $permissionName;
        }

        $this->role->syncPermissions($this->permissions);
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

        $this->role->syncPermissions($this->permissions);
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
        $moduleActions = [];

        foreach (Permission::all() as $permission) {
            $parts  = explode('.', $permission->name);
            $module = $parts[0];
            $action = $parts[1] ?? null;

            // Only map standard 2-part permissions into the toggle table;
            // sub-resource permissions (3+ parts) are skipped to avoid
            // overwriting their parent entry.
            if ($action !== null && count($parts) === 2) {
                $moduleActions[$module][$action] = $permission;
            }
        }

        ksort($moduleActions);

        return view('livewire.roles.roles-edit', [
            'moduleActions' => $moduleActions,
        ])->layout('components.layouts.app');
    }
}
