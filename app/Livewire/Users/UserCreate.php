<?php

namespace App\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Collection;

use Illuminate\Validation\Rules\Password;


class UserCreate extends Component
{
    /* ============================
     | Basic Info
     |============================ */
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';

    /* ============================
     | Meta
     |============================ */
    public string $job_title = '';
    public string $mobile = '';

    /* ============================
     | Roles & Permissions
     |============================ */
    public array $roles = [];
    public string $selectedRole = '';

    /** @var \Illuminate\Support\Collection<int, Permission> */
    public Collection $allPermissions;

    /** @var string[] */
    public array $rolePermissions = [];

    /** @var string[] */
    public array $userPermissions = [];

    /* ============================
     | Lifecycle
     |============================ */
    public function mount(): void
    {
        $this->roles = Role::orderBy('name')->pluck('name')->toArray();
        $this->allPermissions = Permission::orderBy('name')->get();

        if ($this->roles !== []) {
            $this->selectedRole = $this->roles[0];
            $this->loadRolePermissions();
        }
    }

    public function updatedSelectedRole(): void
    {
        $this->resetPermissionState();
        $this->loadRolePermissions();
    }

    /* ============================
     | Permission Logic
     |============================ */
    protected function loadRolePermissions(): void
    {
        $role = Role::where('name', $this->selectedRole)->first();

        $this->rolePermissions = $role
            ? $role->permissions->pluck('name')->all()
            : [];

        // Remove inherited permissions from user overrides
        $this->userPermissions = array_values(
            array_diff($this->userPermissions, $this->rolePermissions)
        );
    }

    protected function resetPermissionState(): void
    {
        $this->rolePermissions = [];
        $this->userPermissions = [];
    }

    public function effectivePermissions(): array
    {
        return array_values(
            array_unique([
                ...$this->rolePermissions,
                ...$this->userPermissions,
            ])
        );
    }

    public function permissionChecked(string $permission): bool
    {
        return in_array($permission, $this->rolePermissions, true)
            || in_array($permission, $this->userPermissions, true);
    }

    /* ============================
     | Persist
     |============================ */
    public function save(): void
    {
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $data = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', Password::min(8)],
            'job_title'  => ['nullable', 'string', 'max:255'],
            'mobile'     => ['nullable', 'string', 'max:50'],
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'name'       => 'default',
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);

        $user->assignRole($this->selectedRole);

        // Only sync user-level overrides
        $user->syncPermissions(
            collect($this->userPermissions)
                ->diff($this->rolePermissions)
                ->values()
                ->all()
        );

        if ($this->job_title) {
            $user->setMeta('job_title', $this->job_title);
        }

        if ($this->mobile) {
            $user->setMeta('mobile', $this->mobile);
        }

        session()->flash('success', 'User created successfully.');

        $this->dispatch(
            'toast',
            message: 'User successfully created.',
            type: 'success'
        );

        $this->redirectRoute('users.index');
    }
}
