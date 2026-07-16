<?php

namespace App\Livewire\Roles;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Campaign;
use App\Models\Role;
use App\Services\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class RolesIndex extends Component
{
    use WithPagination;

    // ── Listing ─────────────────────────────────────────────────────────────
    public int $perPage = 15;

    // ── Modal state ──────────────────────────────────────────────────────────
    public bool   $showModal   = false;
    public string $modalMode   = 'create'; // 'create' | 'edit'
    public ?int   $editRoleId  = null;

    // ── Form fields ──────────────────────────────────────────────────────────
    public string $name             = '';
    public array  $permissions      = [];
    public string $newPermission    = '';
    public array  $selectedCampaigns = [];

    // ── Option lists ─────────────────────────────────────────────────────────
    public array $campaigns = [];

    public function mount(): void
    {
        $this->campaigns = Campaign::orderBy('name')->get(['id', 'name'])->toArray();
    }

    protected function rules(): array
    {
        $uniqueRule = $this->modalMode === 'edit' && $this->editRoleId
            ? 'unique:roles,name,' . $this->editRoleId
            : 'unique:roles,name';

        return [
            'name'          => ['required', 'string', $uniqueRule],
            'permissions'   => ['array'],
            'newPermission' => ['nullable', 'string', 'regex:/^[a-z0-9_]+\.[a-z0-9_]+$/i'],
        ];
    }

    public function updatedPerPage(): void { $this->resetPage(); }

    // ── Modal open / close ───────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEdit(int $roleId): void
    {
        $role = Role::with('permissions', 'campaigns')->findOrFail($roleId);
        $this->editRoleId        = $roleId;
        $this->name              = $role->name;
        $this->permissions       = $role->permissions->pluck('name')->toArray();
        $this->selectedCampaigns = $role->campaigns->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->newPermission     = '';
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
        $this->editRoleId        = null;
        $this->name              = '';
        $this->permissions       = [];
        $this->newPermission     = '';
        $this->selectedCampaigns = [];
        $this->resetErrorBag();
    }

    // ── Permissions helpers ───────────────────────────────────────────────────
    public function toggleModule(array $perms): void
    {
        $allChecked = count(array_diff($perms, $this->permissions)) === 0;
        if ($allChecked) {
            $this->permissions = array_values(array_diff($this->permissions, $perms));
        } else {
            $this->permissions = array_values(array_unique(array_merge($this->permissions, $perms)));
        }
    }

    public function toggleAll(array $allPerms): void
    {
        if (count(array_diff($allPerms, $this->permissions)) === 0) {
            $this->permissions = [];
        } else {
            $this->permissions = array_values(array_unique(array_merge($this->permissions, $allPerms)));
        }
    }

    public function addPermission(): void
    {
        $this->validateOnly('newPermission');

        $permission = Permission::firstOrCreate(['name' => $this->newPermission, 'guard_name' => 'web']);

        if (! in_array($permission->name, $this->permissions)) {
            $this->permissions[] = $permission->name;
        }

        $this->newPermission = '';
    }

    // ── Persist ───────────────────────────────────────────────────────────────
    public function save(): void
    {
        $this->validateOnly('name');
        $this->validateOnly('permissions');

        if ($this->modalMode === 'create') {
            $role = Role::create(['name' => $this->name, 'guard_name' => 'web']);
            $role->syncPermissions($this->permissions);
            $role->campaigns()->sync($this->selectedCampaigns);
            $this->dispatch('toast', message: 'Role created.', type: 'success');
        } else {
            $role = Role::findOrFail($this->editRoleId);
            $role->update(['name' => $this->name]);
            $role->syncPermissions($this->permissions);
            $role->campaigns()->sync($this->selectedCampaigns);
            $this->dispatch('toast', message: 'Role updated.', type: 'success');
        }

        $this->closeModal();
    }

    public function delete(Role $role): void
    {
        $role->delete();
        $this->dispatch('toast', message: 'Role deleted.', type: 'success');
    }

    public function render()
    {
        $groupedPermissions = Permission::where('name', 'not like', 'page.%')
            ->get()
            ->groupBy(fn ($p) => explode('.', $p->name)[0])
            ->toBase();

        return view('livewire.roles.roles-index', [
            'roles'              => Role::withCount('permissions')->paginate($this->perPage),
            'pageGroups'         => PermissionRegistrar::$pageGroups,
            'crudGroups'         => PermissionRegistrar::$crudGroups,
            'groupedPermissions' => $groupedPermissions,
            'allCampaigns'       => $this->campaigns,
        ])->layout('components.layouts.admin', ['heading' => 'Roles & Permissions']);
    }
}
