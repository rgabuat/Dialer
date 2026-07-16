<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Lead;
use App\Models\UserGroup;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules\Password;

class UserShow extends Component
{
    use WithPagination;

    public User $user;

    // ── Edit modal ────────────────────────────────────────────────────────────
    public bool    $showModal        = false;
    public bool    $confirmingDelete = false;
    public string  $first_name       = '';
    public string  $last_name        = '';
    public string  $email            = '';
    public ?string $password         = null;
    public ?string $job_title        = null;
    public ?string $mobile           = null;
    public string  $selectedRole     = '';
    public ?int    $selectedUserGroup = null;
    public array   $roles            = [];
    public array   $userGroups       = [];

    public function mount(User $user): void
    {
        $this->user       = $user->load(['agentStatus.statusType', 'roles', 'userGroup']);
        $this->roles      = Role::orderBy('name')->pluck('name')->toArray();
        $this->userGroups = UserGroup::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function openEdit(): void
    {
        $this->first_name        = $this->user->first_name;
        $this->last_name         = $this->user->last_name;
        $this->email             = $this->user->email;
        $this->password          = null;
        $this->job_title         = $this->user->getMeta('job_title');
        $this->mobile            = $this->user->getMeta('mobile');
        $this->selectedRole      = $this->user->roles->first()?->name ?? '';
        $this->selectedUserGroup = $this->user->user_group_id;
        $this->confirmingDelete  = false;
        $this->showModal         = true;
    }

    public function closeModal(): void
    {
        $this->showModal        = false;
        $this->confirmingDelete = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $data = $this->validate([
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'email', 'unique:users,email,' . $this->user->id],
            'password'          => ['nullable', Password::min(8)],
            'job_title'         => ['nullable', 'string', 'max:255'],
            'mobile'            => ['nullable', 'string', 'max:50'],
            'selectedRole'      => ['nullable', 'string', 'exists:roles,name'],
            'selectedUserGroup' => ['nullable', 'integer', 'exists:user_groups,id'],
        ]);

        $this->user->update([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            ...(filled($data['password']) ? ['password' => Hash::make($data['password'])] : []),
        ]);

        $this->user->setMeta('job_title', $this->job_title);
        $this->user->setMeta('mobile',    $this->mobile);

        if ($this->selectedRole) {
            $this->user->syncRoles([$this->selectedRole]);
        }

        $this->user->update(['user_group_id' => $this->selectedUserGroup]);

        // Reload user so profile card reflects changes
        $this->user = $this->user->fresh(['agentStatus.statusType', 'roles', 'userGroup']);

        $this->dispatch('toast', message: 'User updated successfully.', type: 'success');
        $this->closeModal();
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        DB::transaction(fn () => $this->user->delete());

        $this->dispatch('toast', message: 'User deleted.', type: 'success');
        $this->redirectRoute('users.index');
    }

    public function render()
    {
        $leads = Lead::where('last_actioned_by', $this->user->id)
            ->with('callList.campaign')
            ->latest('updated_at')
            ->paginate(15);

        return view('livewire.users.user-show', [
            'leads'    => $leads,
            'jobTitle' => $this->user->getMeta('job_title'),
            'mobile'   => $this->user->getMeta('mobile'),
        ])->layout('components.layouts.admin', ['heading' => $this->user->first_name . ' ' . $this->user->last_name]);
    }
}
