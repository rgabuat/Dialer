<div>
    {{-- MAIN FORM --}}
    <form wire:submit.prevent="save">
        <div class="flex-1 rounded-2xl bg-gradient-to-b from-[#151a20] to-[#0f1115] border border-white/5 p-8">

            {{-- Header --}}
            <div class="mb-8">
                <h1 class="text-2xl font-semibold text-white light:text-fg mb-1">Edit User</h1>
                <p class="text-sm text-fg-muted">
                    Update user account details and preferences.
                </p>
            </div>

            <div class="max-w-4xl space-y-10">
                {{-- BASICS --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                    <div>
                        <h2 class="font-medium text-white">Basics</h2>
                        <p class="text-sm text-fg-muted">Core user information.</p>
                    </div>

                    <div class="md:col-span-3 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm text-fg-muted">First name</label>
                                <input wire:model.defer="first_name" type="text"
                                    class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none" />
                            </div>

                            <div>
                                <label class="text-sm text-fg-muted">Last name</label>
                                <input wire:model.defer="last_name" type="text"
                                    class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none" />
                            </div>
                        </div>

                        <div>
                            <label class="text-sm text-fg-muted">Email</label>
                            <input wire:model.defer="email" type="email"
                                class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none" />
                        </div>
                    </div>
                </div>

                {{-- ACCESS --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                    <div>
                        <h2 class="font-medium text-white">Access</h2>
                        <p class="text-sm text-fg-muted">Role & password.</p>
                    </div>

                    <div class="md:col-span-3 space-y-6">
                        <div>
                            <label class="text-sm text-fg-muted">Role</label>
                            <select wire:model="selectedRole"
                                class="mt-1 w-full rounded-lg bg-neutral-950 border border-neutral-800
                                       px-3 py-2 text-white focus:border-indigo-500 focus:outline-none text-sm">
                                <option value="">No role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @error('selectedRole')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-fg-muted">User Group</label>
                            <select wire:model.defer="selectedUserGroup"
                                class="mt-1 w-full rounded-lg bg-neutral-950 border border-neutral-800
                                       px-3 py-2 text-white focus:border-indigo-500 focus:outline-none text-sm">
                                <option value="">No group</option>
                                @foreach ($userGroups as $group)
                                    <option value="{{ $group['id'] }}">{{ $group['name'] }}</option>
                                @endforeach
                            </select>
                            @error('selectedUserGroup')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="text-sm text-fg-muted">New password <span class="text-zinc-600">(leave blank to keep current)</span></label>
                            <input wire:model.defer="password" type="password"
                                class="mt-1 w-full rounded-lg bg-neutral-950 border border-neutral-800
                                       px-3 py-2 text-white focus:border-indigo-500 focus:outline-none" />
                            @error('password')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- ACTIONS --}}
                <div class="flex justify-end pt-8">
                    <button type="submit"
                        class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-500">
                        Save Changes
                    </button>
                </div>

                {{-- DANGER ZONE --}}
                <div class="mt-12 border-t border-surface pt-8">
                    <h3 class="text-sm font-medium text-accent-red">
                        Danger zone
                    </h3>

                    <p class="mt-1 text-sm text-fg-muted">
                        Deleting a user is permanent and cannot be undone.
                    </p>

                    <button type="button" wire:click="confirmDelete"
                        class="mt-4 rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">
                        Delete user
                    </button>
                </div>

            </div>
        </div>
    </form>

    {{-- DELETE MODAL --}}
    @if ($confirmingDelete)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/70
                transition-opacity duration-200 ease-out">
            <div
                class="w-full max-w-md rounded-lg bg-surface p-6 shadow-xl
                    transform transition-all duration-200 ease-out
                    scale-95 opacity-0
                    animate-modal-in">
                <h2 class="text-lg font-semibold text-fg">
                    Delete user
                </h2>

                <p class="mt-2 text-sm text-fg-muted">
                    Are you sure you want to delete
                    <span class="font-medium text-fg-2">
                        {{ $user->first_name }} {{ $user->last_name }}
                    </span>?
                    This action cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <button type="button" wire:click="$set('confirmingDelete', false)"
                        class="rounded-md bg-surface-2 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-700">
                        Cancel
                    </button>

                    <button type="button" wire:click="delete"
                        class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
