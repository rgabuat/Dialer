<div class="space-y-4 p-6 stagger-children" wire:poll.5s="$refresh">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Users</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage user accounts, monitor live calls, and control access.</p>
    </div>

    {{-- USERS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-bold text-fg text-base">Accounts</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Listen &amp; Barge buttons appear automatically when an agent is
                    on a live call.</p>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="$refresh" title="Refresh call status"
                    class="p-1.5 rounded-md text-fg-muted hover:text-fg hover:bg-hover transition">
                    <x-heroicon-o-arrow-path class="w-4 h-4" wire:loading.class="animate-spin" />
                </button>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search users..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                <button wire:click="openCreate"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    New User
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data="{
            _fn: null,
            init() {
                this._fn = () => {
                    const pg = this.$el.nextElementSibling;
                    this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
                };
                this._fn();
                window.addEventListener('resize', this._fn);
            },
            destroy() { window.removeEventListener('resize', this._fn); }
        }">
            <table class="min-w-full text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Agent</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Group</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php
                            $status = $user->agentStatus?->statusType;
                            $activeCall = $activeCalls->get($user->id);
                            $isOnCall = (bool) $activeCall;
                            $role = $user->roles->first();
                            $statusColor = $status?->color ?? '#6b7280';
                        @endphp
                        <tr class="border-surface border-b transition group hover:bg-hover cursor-pointer"
                            onclick="window.location='{{ route('user.show', $user) }}'">

                            {{-- Agent (avatar + name + email) --}}
                            <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                                <a href="{{ route('user.show', $user) }}" wire:navigate class="flex items-center gap-3">
                                    {{-- Avatar with live-call ring --}}
                                    <div class="relative shrink-0">
                                        <span
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold {{ avatarColor($user->email) }}">
                                            {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                        </span>
                                        @if ($isOnCall)
                                            <span class="absolute -bottom-0.5 -right-0.5 flex h-3 w-3">
                                                <span
                                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                <span
                                                    class="relative inline-flex rounded-full h-3 w-3 bg-green-500 border-2 border-surface"></span>
                                            </span>
                                        @elseif ($status)
                                            <span
                                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-surface"
                                                style="background-color: {{ $statusColor }}"></span>
                                        @else
                                            <span
                                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-surface bg-zinc-600"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg text-sm leading-tight">
                                            {{ $user->first_name }} {{ $user->last_name }}
                                        </div>
                                        <div class="text-zinc-500 text-xs truncate">{{ $user->email }}</div>
                                    </div>
                                </a>
                            </td>

                            {{-- Role --}}
                            <td class="px-5 py-3.5">
                                @if ($role)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded-md text-xs font-medium">
                                        {{ $role->name }}
                                    </span>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Group --}}
                            <td class="px-5 py-3.5">
                                @if ($user->userGroup)
                                    <span class="text-fg-muted text-xs">{{ $user->userGroup->name }}</span>
                                @else
                                    <span class="text-zinc-600 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                @if ($isOnCall)
                                    <div>
                                        <span
                                            class="inline-flex items-center gap-1.5 bg-green-500/10 text-green-400 px-2 py-0.5 rounded-md text-xs font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span>
                                            On Call
                                        </span>
                                        <div class="text-[10px] text-zinc-500 mt-0.5">
                                            {{ $activeCall->direction === 'inbound' ? '↙ Inbound' : '↗ Outbound' }}
                                            @if ($activeCall->contact_phone)
                                                · {{ $activeCall->contact_phone }}
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($status)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium"
                                        style="background-color: {{ $statusColor }}18; color: {{ $statusColor }}">
                                        <span class="w-1.5 h-1.5 rounded-full"
                                            style="background-color: {{ $statusColor }}"></span>
                                        {{ $status->name }}
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-zinc-700/40 text-zinc-500 px-2 py-0.5 rounded-md text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-600"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-3.5" onclick="event.stopPropagation()">
                                <div class="flex items-center justify-end gap-2">

                                    {{-- Listen (silent monitoring) — only when on a call --}}
                                    @if ($isOnCall)
                                        <button wire:click="monitorAgent({{ $user->id }}, 'listen')"
                                            wire:loading.attr="disabled"
                                            title="Listen silently — agent and caller will not know"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-blue-600/15 hover:bg-blue-600/30 text-blue-400 text-xs font-medium transition">
                                            <x-heroicon-o-speaker-wave class="w-3.5 h-3.5" />
                                            Listen
                                        </button>

                                        {{-- Barge (join call audibly) --}}
                                        <button wire:click="monitorAgent({{ $user->id }}, 'barge')"
                                            wire:loading.attr="disabled" title="Barge — join the call and speak"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-amber-500/15 hover:bg-amber-500/30 text-amber-400 text-xs font-medium transition">
                                            <x-heroicon-o-megaphone class="w-3.5 h-3.5" />
                                            Barge
                                        </button>
                                    @endif

                                    {{-- Edit --}}
                                    <button wire:click="openEdit({{ $user->id }})"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-md bg-surface-2 hover:bg-hover text-fg-muted hover:text-fg text-xs font-medium transition">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                        Edit
                                    </button>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$users" label="users" />

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         FULL-SCREEN SLIDE-OVER MODAL
    ══════════════════════════════════════════════════════════════ --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex" x-data x-on:keydown.escape.window="$wire.closeModal()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Panel (slides in from right) --}}
            <div class="relative ml-auto h-full w-full max-w-2xl bg-surface border-l border-surface flex flex-col shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-surface shrink-0">
                    <div>
                        <h2 class="font-semibold text-fg text-base">
                            {{ $modalMode === 'create' ? 'New User' : 'Edit User' }}
                        </h2>
                        <p class="text-xs text-fg-muted mt-0.5">
                            {{ $modalMode === 'create' ? 'Create and configure a new user account.' : 'Update user account details and access.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal"
                        class="text-fg-muted hover:text-fg transition p-1 rounded-md hover:bg-hover">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

                    {{-- Basics --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Basics</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-fg-muted mb-1">First name</label>
                                    <input wire:model.defer="first_name" type="text"
                                        class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                                    @error('first_name')
                                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-fg-muted mb-1">Last name</label>
                                    <input wire:model.defer="last_name" type="text"
                                        class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                                    @error('last_name')
                                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Email</label>
                                <input wire:model.defer="email" type="email"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                                @error('email')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-surface"></div>

                    {{-- Access --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Access</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">
                                    {{ $modalMode === 'create' ? 'Temporary password' : 'New password' }}
                                    @if ($modalMode === 'edit')
                                        <span class="text-zinc-600 font-normal">(leave blank to keep current)</span>
                                    @endif
                                </label>
                                <input wire:model.defer="password" type="password"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                                @error('password')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Role</label>
                                <select wire:model.live="selectedRole"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none">
                                    <option value="">No role</option>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                    @endforeach
                                </select>
                                @error('selectedRole')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">User Group</label>
                                <select wire:model.defer="selectedUserGroup"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none">
                                    <option value="">Select group</option>
                                    @foreach ($userGroups as $group)
                                        <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                                    @endforeach
                                </select>
                                @error('selectedUserGroup')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-surface"></div>

                    {{-- Additional info --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Additional Info
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Job title</label>
                                <input wire:model.defer="job_title" type="text"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Mobile</label>
                                <input wire:model.defer="mobile" type="text"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                            </div>
                        </div>
                    </div>

                    {{-- Danger zone (edit only) --}}
                    @if ($modalMode === 'edit')
                        <div class="border-t border-surface pt-6">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-red-500 mb-2">Danger Zone
                            </h3>
                            <p class="text-xs text-fg-muted mb-3">Deleting a user is permanent and cannot be undone.
                            </p>
                            <button type="button" wire:click="confirmDelete"
                                class="px-3 py-1.5 rounded-md bg-red-600/15 hover:bg-red-600/30 text-red-400 text-sm font-medium transition">
                                Delete user
                            </button>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                        {{ $modalMode === 'create' ? 'Create User' : 'Save Changes' }}
                    </button>
                </div>

            </div>
        </div>

        {{-- Delete confirmation --}}
        @if ($confirmingDelete)
            <div class="fixed inset-0 z-[60] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/70"></div>
                <div class="relative w-full max-w-sm mx-4 bg-surface border border-surface rounded-xl p-6 shadow-2xl">
                    <h3 class="font-semibold text-fg text-base">Delete user</h3>
                    <p class="mt-2 text-sm text-fg-muted">This action cannot be undone.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="$set('confirmingDelete', false)"
                            class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete"
                            class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
