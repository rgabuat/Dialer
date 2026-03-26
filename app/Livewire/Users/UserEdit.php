<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\UserGroup;
use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules\Password;

class UserEdit extends Component
{
    public User $user;
    public bool $confirmingDelete = false;

    // User fields
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public ?string $password = null;

    // Meta fields
    public ?string $job_title = null;
    public ?string $mobile = null;

    // Role
    public array $roles = [];
    public string $selectedRole = '';

    // User Group
    public array $userGroups = [];
    public ?int $selectedUserGroup = null;

    public function mount(User $user)
    {
        $this->user = $user;

        // Fill user fields
        $this->first_name = $user->first_name;
        $this->last_name  = $user->last_name;
        $this->email      = $user->email;

        // Fill meta
        $this->job_title = $user->getMeta('job_title');
        $this->mobile    = $user->getMeta('mobile');

        // Fill role
        $this->roles        = Role::orderBy('name')->pluck('name')->toArray();
        $this->selectedRole = $user->roles->first()?->name ?? '';

        // Fill user group
        $this->userGroups        = UserGroup::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
        $this->selectedUserGroup = $user->user_group_id;
    }

    public function save()
    {
        // 🔹 Ensure one batch per update action
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $data = $this->validate([
            'first_name'   => ['required', 'string', 'max:255'],
            'last_name'    => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'unique:users,email,' . $this->user->id],
            'password'     => ['nullable', Password::min(8)],
            'job_title'    => ['nullable', 'string', 'max:255'],
            'mobile'       => ['nullable', 'string', 'max:50'],
            'selectedRole' => ['nullable', 'string', 'exists:roles,name'],
            'selectedUserGroup' => ['nullable', 'integer', 'exists:user_groups,id'],
        ]);

        // 🔹 Update user (UserObserver will log diffs)
        $this->user->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            ...(filled($data['password']) ? [
                'password' => Hash::make($data['password']),
            ] : []),
        ]);

        // 🔹 Update meta (UsersMetaObserver will log diffs)
        $this->user->setMeta('job_title', $this->job_title);
        $this->user->setMeta('mobile', $this->mobile);

        // 🔹 Sync role
        if ($this->selectedRole) {
            $this->user->syncRoles([$this->selectedRole]);
        }

        // 🔹 Update user group
        $this->user->update(['user_group_id' => $this->selectedUserGroup]);

        $this->dispatch(
            'toast',
            message: 'User updated successfully.',
            type: 'success'
        );

        return redirect()->route('users.index');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete()
    {
        
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        DB::transaction(function () {
            $this->user->delete(); // 🔥 UserObserver@deleted fires
        });

        $this->dispatch(
            'toast',
            message: 'User deleted successfully.',
            type: 'success'
        );

        return redirect()->route('users.index');
    }

    

    public function render()
    {
        return view('livewire.users.user-edit')
            ->layout('components.layouts.app');
    }
}
