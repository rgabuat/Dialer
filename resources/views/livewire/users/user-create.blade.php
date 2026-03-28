<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New User</h1>
            <p class="text-sm text-fg-muted">
                Create and configure a new user account.
            </p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- BASICS --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Basics</h2>
                    <p class="text-sm text-fg-muted">Core user information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">First name</label>
                            <input wire:model.defer="first_name" type="text"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('first_name')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-fg-muted">Last name</label>
                            <input wire:model.defer="last_name" type="text"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('last_name')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="text-sm text-fg-muted">Email</label>
                        <input wire:model.defer="email" type="email"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('email')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ACCESS --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Access</h2>
                    <p class="text-sm text-fg-muted">Credentials & role.</p>
                </div>

                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Temporary password</label>
                        <input wire:model.defer="password" type="password"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('password')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-sm text-fg-muted">Role</label>
                        <select wire:model.live="selectedRole"
                            class="mt-1 w-full rounded-md bg-surface border border-surface
                                px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                            <option value="">Select role</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- USER GROUP --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">User Group</h2>
                    <p class="text-sm text-fg-muted">Assign the user to a group (required). The group determines which campaigns the user can access.</p>
                </div>
                <div class="md:col-span-3 space-y-3">
                    <select wire:model.defer="selectedUserGroup"
                        class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                        <option value="">Select user group</option>
                        @foreach ($userGroups as $group)
                            <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedUserGroup')
                        <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- META --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <h2 class="font-medium">Additional info</h2>
                    <p class="text-sm text-fg-muted">Optional metadata.</p>
                </div>

                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Job title</label>
                        <input wire:model.defer="job_title" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                    </div>

                    <div>
                        <label class="text-sm text-fg-muted">Mobile</label>
                        <input wire:model.defer="mobile" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="flex justify-end pt-8">
                <button type="submit"
                    class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500 focus:ring-2 focus:ring-indigo-400">
                    Create User
                </button>
            </div>

        </div>
    </div>
</form>
