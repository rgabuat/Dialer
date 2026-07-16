<div class="p-6 space-y-6 stagger-children">

    {{-- Back + Edit header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-fg-muted hover:text-fg text-sm transition">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Back to Users
        </a>
        <button wire:click="openEdit"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-md text-white text-sm font-medium transition">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit User
        </button>
    </div>

    {{-- Profile card --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="px-6 py-5 flex items-start gap-5">

            {{-- Avatar --}}
            <div class="relative shrink-0">
                <span
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full text-xl font-bold {{ avatarColor($user->email) }}">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </span>
                @php $status = $user->agentStatus?->statusType; @endphp
                @if ($status)
                    @php $dotColor = $status->color ?? '#6b7280'; @endphp
                    <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-surface"
                        style="background-color: {{ $dotColor }}"></span>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-fg leading-tight">
                    {{ $user->first_name }} {{ $user->last_name }}
                </h2>
                <p class="text-sm text-fg-muted mt-0.5">{{ $user->email }}</p>

                <div class="flex flex-wrap items-center gap-2 mt-3">
                    {{-- Role --}}
                    @foreach ($user->roles as $role)
                        <span
                            class="inline-flex items-center gap-1 bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded-md text-xs font-medium">
                            <x-heroicon-o-shield-check class="w-3 h-3" />
                            {{ $role->name }}
                        </span>
                    @endforeach

                    {{-- User Group --}}
                    @if ($user->userGroup)
                        <span
                            class="inline-flex items-center gap-1 bg-zinc-700/50 text-zinc-300 px-2 py-0.5 rounded-md text-xs font-medium">
                            <x-heroicon-o-user-group class="w-3 h-3" />
                            {{ $user->userGroup->name }}
                        </span>
                    @endif

                    {{-- Agent status --}}
                    @if ($status)
                        @php
                            $color = $status->color ?? '#6b7280';
                            $bgAlpha = $color . '1a';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium"
                            style="background-color: {{ $bgAlpha }}; color: {{ $color }}">
                            <span class="w-1.5 h-1.5 rounded-full"
                                style="background-color: {{ $color }}"></span>
                            {{ $status->name }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 bg-zinc-700/40 text-zinc-500 px-2 py-0.5 rounded-md text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-600"></span>
                            Offline
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta grid --}}
        <div class="border-t border-surface grid grid-cols-2 sm:grid-cols-4 divide-x divide-surface">
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">ID</p>
                <p class="mt-1 text-sm font-semibold text-fg">{{ $user->id }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Job Title</p>
                <p class="mt-1 text-sm text-fg">{{ $jobTitle ?: '—' }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Mobile</p>
                <p class="mt-1 text-sm text-fg">{{ $mobile ?: '—' }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Joined</p>
                <p class="mt-1 text-sm text-fg">{{ $user->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Leads --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
        <div class="px-5 py-4 border-b border-surface">
            <h3 class="font-semibold text-fg text-sm">Leads</h3>
            <p class="text-xs text-zinc-500 mt-0.5">{{ $leads->total() }} leads last actioned by this agent.</p>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-surface">
                    <tr class="border-b border-surface text-zinc-500 text-xs uppercase tracking-wider font-semibold">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Phone</th>
                        <th class="px-5 py-3 text-left">Campaign</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Last Actioned</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        @php
                            $statusBadge = match ($lead->status) {
                                'new' => 'bg-blue-500/10 text-blue-400',
                                'contacted' => 'bg-yellow-500/10 text-yellow-400',
                                'converted' => 'bg-green-500/10 text-green-400',
                                'dnc' => 'bg-red-500/10 text-red-400',
                                default => 'bg-zinc-700/40 text-zinc-400',
                            };
                        @endphp
                        <tr class="border-b border-surface hover:bg-hover transition">
                            <td class="px-5 py-3 font-medium text-fg">
                                {{ trim($lead->first_name . ' ' . $lead->last_name) ?: '—' }}
                                @if ($lead->email)
                                    <div class="text-xs text-zinc-500">{{ $lead->email }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-fg-muted text-xs">{{ $lead->phone ?: '—' }}</td>
                            <td class="px-5 py-3 text-fg-muted text-xs">
                                {{ $lead->callList?->campaign?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3">
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $statusBadge }}">
                                    {{ ucfirst($lead->status ?? 'unknown') }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-fg-muted text-xs">
                                {{ $lead->updated_at?->format('M j, Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-zinc-500 italic text-sm">
                                No leads actioned yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$leads" label="leads" />
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         EDIT SLIDE-OVER MODAL
    ══════════════════════════════════════════════════════════════ --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex" x-data x-on:keydown.escape.window="$wire.closeModal()">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>
            <div class="relative ml-auto h-full w-full max-w-2xl bg-surface border-l border-surface flex flex-col shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                <div class="flex items-center justify-between px-6 py-4 border-b border-surface shrink-0">
                    <div>
                        <h2 class="font-semibold text-fg text-base">Edit User</h2>
                        <p class="text-xs text-fg-muted mt-0.5">Update account details and access.</p>
                    </div>
                    <button wire:click="closeModal"
                        class="text-fg-muted hover:text-fg transition p-1 rounded-md hover:bg-hover">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

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

                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Access</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">New password <span
                                        class="text-zinc-600 font-normal">(leave blank to keep current)</span></label>
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
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-surface"></div>

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

                    <div class="border-t border-surface pt-6">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-red-500 mb-2">Danger Zone</h3>
                        <p class="text-xs text-fg-muted mb-3">Deleting a user is permanent and cannot be undone.</p>
                        <button type="button" wire:click="confirmDelete"
                            class="px-3 py-1.5 rounded-md bg-red-600/15 hover:bg-red-600/30 text-red-400 text-sm font-medium transition">
                            Delete user
                        </button>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>

            </div>
        </div>

        @if ($confirmingDelete)
            <div class="fixed inset-0 z-[60] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/70"></div>
                <div class="relative w-full max-w-sm mx-4 bg-surface border border-surface rounded-xl p-6 shadow-2xl">
                    <h3 class="font-semibold text-fg text-base">Delete user</h3>
                    <p class="mt-2 text-sm text-fg-muted">This action cannot be undone.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="$set('confirmingDelete', false)"
                            class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">Cancel</button>
                        <button type="button" wire:click="delete"
                            class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">Delete</button>
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
