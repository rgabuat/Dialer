<div class="max-w-4xl mx-auto p-6">

    <h1 class="text-lg font-semibold text-white mb-6">
        Edit Role: <span class="text-blue-400">{{ $role->name }}</span>
    </h1>

    {{-- Flash message --}}
    @if (session()->has('success'))
        <div class="mb-4 rounded bg-green-700/30 border border-green-600 px-4 py-2 text-sm text-green-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Role Name --}}
    <div class="mb-6">
        <label class="block text-sm text-zinc-400 mb-1">Role Name</label>
        <input
            type="text"
            wire:model="name"
            class="w-full rounded bg-zinc-900 border border-zinc-800
                   px-3 py-2 text-white focus:outline-none
                   focus:ring focus:ring-blue-500/20"
        >
        @error('name')
            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Permissions --}}
    <div class="mb-6">
        <h2 class="text-sm font-semibold text-zinc-300 mb-3">Permissions</h2>

        @forelse($groupedPermissions as $module => $perms)
            <div class="mb-4 border border-zinc-800 rounded-lg p-4">

                <div class="text-xs uppercase font-semibold text-zinc-400 mb-3">
                    {{ $module }}
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($perms as $permission)
                        <div class="flex items-center justify-between gap-2">
                            <label class="flex items-center gap-2 text-sm text-white">
                                <input
                                    type="checkbox"
                                    wire:model="permissions"
                                    value="{{ $permission->name }}"
                                    class="rounded border-zinc-700 bg-zinc-900
                                           text-blue-500 focus:ring-blue-500/30"
                                >
                                {{ $permission->name }}
                            </label>

                            <button
                                wire:click="deletePermission({{ $permission->id }})"
                                wire:confirm="Delete permission '{{ $permission->name }}' from the system? This affects all roles."
                                class="text-zinc-600 hover:text-red-400 transition text-xs"
                                title="Delete permission"
                            >
                                &times;
                            </button>
                        </div>
                    @endforeach
                </div>

            </div>
        @empty
            <div class="text-sm italic text-zinc-500">No permissions available yet.</div>
        @endforelse
    </div>

    {{-- Add New Permission --}}
    <div class="mb-8 border border-zinc-800 rounded-lg p-4">
        <h2 class="text-sm font-semibold text-zinc-300 mb-3">Add New Permission</h2>
        <p class="text-xs text-zinc-500 mb-3">Use dot notation, e.g. <code class="text-zinc-300">users.create</code></p>

        <div class="flex gap-2">
            <input
                type="text"
                wire:model="newPermission"
                wire:keydown.enter="addPermission"
                placeholder="module.action"
                class="flex-1 rounded bg-zinc-900 border border-zinc-800
                       px-3 py-2 text-white text-sm focus:outline-none
                       focus:ring focus:ring-blue-500/20"
            >
            <button
                wire:click="addPermission"
                class="px-4 py-2 bg-zinc-700 hover:bg-zinc-600
                       text-white text-sm rounded transition"
            >
                Add
            </button>
        </div>

        @error('newPermission')
            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button
            wire:click="save"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500
                   text-white text-sm rounded transition"
        >
            Save Changes
        </button>

        <a
            href="{{ route('roles.index') }}"
            class="text-sm text-zinc-400 hover:text-zinc-200"
        >
            Cancel
        </a>
    </div>

</div>
