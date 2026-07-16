<?php

namespace App\Livewire\Users;

use App\Http\Controllers\TwilioController;
use App\Models\Conversation;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsersIndex extends Component
{
    use WithPagination;

    // ── Listing ──────────────────────────────────────────────────────────────
    public string $search = '';

    public int $perPage = 10;

    protected $queryString = ['search'];

    // ── Modal state ──────────────────────────────────────────────────────────
    public bool $showModal = false;

    public string $modalMode = 'create'; // 'create' | 'edit'

    public ?int $editUserId = null;

    public bool $confirmingDelete = false;

    // ── Form fields ──────────────────────────────────────────────────────────
    public string $first_name = '';

    public string $last_name = '';

    public string $email = '';

    public ?string $password = null;

    public ?string $job_title = null;

    public ?string $mobile = null;

    public string $selectedRole = '';

    public ?int $selectedUserGroup = null;

    // ── Option lists ─────────────────────────────────────────────────────────
    public array $roles = [];

    public array $userGroups = [];

    public function mount(): void
    {
        $this->roles = Role::orderBy('name')->pluck('name')->toArray();
        $this->userGroups = UserGroup::where('is_active', true)->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    // ── Modal open / close ────────────────────────────────────────────────────
    public function openCreate(): void
    {
        $this->resetForm();
        $this->modalMode = 'create';
        $this->showModal = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::findOrFail($userId);
        $this->editUserId = $userId;
        $this->first_name = $user->first_name;
        $this->last_name = $user->last_name;
        $this->email = $user->email;
        $this->password = null;
        $this->job_title = $user->getMeta('job_title');
        $this->mobile = $user->getMeta('mobile');
        $this->selectedRole = $user->roles->first()?->name ?? '';
        $this->selectedUserGroup = $user->user_group_id;
        $this->confirmingDelete = false;
        $this->modalMode = 'edit';
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editUserId = null;
        $this->first_name = '';
        $this->last_name = '';
        $this->email = '';
        $this->password = null;
        $this->job_title = null;
        $this->mobile = null;
        $this->selectedRole = '';
        $this->selectedUserGroup = null;
        $this->confirmingDelete = false;
        $this->resetErrorBag();
    }

    // ── Persist ───────────────────────────────────────────────────────────────
    public function save(): void
    {
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        if ($this->modalMode === 'create') {
            $data = $this->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', Password::min(8)],
                'job_title' => ['nullable', 'string', 'max:255'],
                'mobile' => ['nullable', 'string', 'max:50'],
                'selectedUserGroup' => ['required', 'integer', 'exists:user_groups,id'],
            ]);

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'name' => 'default',
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($this->selectedRole);
            $user->update(['user_group_id' => $this->selectedUserGroup]);
            if ($this->job_title) {
                $user->setMeta('job_title', $this->job_title);
            }
            if ($this->mobile) {
                $user->setMeta('mobile', $this->mobile);
            }

            $this->dispatch('toast', message: 'User created successfully.', type: 'success');
        } else {
            $user = User::findOrFail($this->editUserId);

            $data = $this->validate([
                'first_name' => ['required', 'string', 'max:255'],
                'last_name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email,'.$user->id],
                'password' => ['nullable', Password::min(8)],
                'job_title' => ['nullable', 'string', 'max:255'],
                'mobile' => ['nullable', 'string', 'max:50'],
                'selectedRole' => ['nullable', 'string', 'exists:roles,name'],
                'selectedUserGroup' => ['nullable', 'integer', 'exists:user_groups,id'],
            ]);

            $user->update([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                ...(filled($data['password']) ? ['password' => Hash::make($data['password'])] : []),
            ]);

            $user->setMeta('job_title', $this->job_title);
            $user->setMeta('mobile', $this->mobile);

            if ($this->selectedRole) {
                $user->syncRoles([$this->selectedRole]);
            }

            $user->update(['user_group_id' => $this->selectedUserGroup]);

            $this->dispatch('toast', message: 'User updated successfully.', type: 'success');
        }

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

        $user = User::findOrFail($this->editUserId);

        DB::transaction(fn () => $user->delete());

        $this->dispatch('toast', message: 'User deleted successfully.', type: 'success');
        $this->closeModal();
    }

    // ── Call monitoring ───────────────────────────────────────────────────────
    public function monitorAgent(int $userId, string $mode): void
    {
        // mode: 'listen' (muted, agent/customer unaware) or 'barge' (can speak)
        $conversation = Conversation::where('assigned_to', $userId)
            ->where('status', 'in_progress')
            ->whereNotNull('call_sid')
            ->latest('started_at')
            ->first();

        if (! $conversation) {
            $this->dispatch('toast', message: 'Agent is not on an active call.', type: 'error');

            return;
        }

        try {
            $controller = app(TwilioController::class);
            $request = Request::create('/api/call/monitor', 'POST', [
                'user_id' => $userId,
                'mode' => $mode,
            ]);
            $request->setUserResolver(fn () => auth()->user());

            $response = $controller->monitorCall($request);
            $body = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 200) {
                $agent = User::findOrFail($userId);
                $this->dispatch('monitoring-started',
                    mode: $mode,
                    agent_name: $agent->first_name.' '.$agent->last_name,
                    direction: $conversation->direction,
                    contact_phone: $conversation->contact_phone ?? '—',
                    started_at: $conversation->started_at?->toIso8601String(),
                    conference_name: $body['conference_name'] ?? null,
                );
            } else {
                $this->dispatch('toast', message: $body['message'] ?? 'Monitor session failed.', type: 'error');
            }
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Error: '.$e->getMessage(), type: 'error');
        }
    }

    public function render()
    {
        // Active call SIDs keyed by assigned_to user_id
        $activeCalls = Conversation::where('status', 'in_progress')
            ->whereNotNull('assigned_to')
            ->whereNotNull('call_sid')
            ->get(['assigned_to', 'call_sid', 'contact_phone', 'direction'])
            ->keyBy('assigned_to');

        return view('livewire.users.users-index', [
            'users' => User::query()
                ->with(['agentStatus.statusType', 'roles', 'userGroup'])
                ->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                })
                ->latest()
                ->paginate($this->perPage),
            'activeCalls' => $activeCalls,
        ])->layout('components.layouts.admin', ['heading' => 'Users']);
    }
}
