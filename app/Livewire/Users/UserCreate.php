<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\UserGroup;
use Livewire\Component;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

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

    /* ============================
     | User Group
     |============================ */
    public array $userGroups = [];
    public ?int $selectedUserGroup = null;

    /* ============================
     | Lifecycle
     |============================ */
    public function mount(): void
    {
        $this->roles      = Role::orderBy('name')->pluck('name')->toArray();
        $this->userGroups = UserGroup::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
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
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:users,email'],
            'password'          => ['required', Password::min(8)],
            'job_title'         => ['nullable', 'string', 'max:255'],
            'mobile'            => ['nullable', 'string', 'max:50'],
            'selectedUserGroup' => ['required', 'integer', 'exists:user_groups,id'],
        ]);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'name'       => 'default',
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
        ]);

        $user->assignRole($this->selectedRole);

        $user->update(['user_group_id' => $this->selectedUserGroup]);

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

    public function render()
    {
        return view('livewire.users.user-create')
            ->layout('components.layouts.admin', ['heading' => 'Create User']);
    }
}
