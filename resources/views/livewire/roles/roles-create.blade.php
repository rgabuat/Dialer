<div class="max-w-4xl mx-auto p-6">

    <h1 class="text-lg font-semibold text-white mb-6">
        Create Role
    </h1>

    {{-- Role Name --}}
    <div class="mb-6">
        <label class="block text-sm text-zinc-400 mb-1">
            Role Name
        </label>

        <input
            type="text"
            wire:model.defer="name"
            placeholder="e.g. Admin, Editor"
            class="w-full rounded bg-zinc-900 border border-zinc-800
                   px-3 py-2 text-white focus:outline-none
                   focus:ring focus:ring-blue-500/20"
        >

        @error('name')
            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Permissions --}}
    <div class="mb-8">
        <h2 class="text-sm font-semibold text-zinc-300 mb-3">
            Assign Permissions
        </h2>

        @forelse($groupedPermissions as $module => $perms)
            <div class="mb-5 border border-zinc-800 rounded-lg p-4">

                <div class="text-xs uppercase font-semibold text-zinc-400 mb-3">
                    {{ $module }}
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach($perms as $permission)
                        <label class="flex items-center gap-2 text-sm text-white">
                            <input
                                type="checkbox"
                                wire:model.defer="permissions"
                                value="{{ $permission->name }}"
                                class="rounded border-zinc-700 bg-zinc-900
                                       text-blue-500 focus:ring-blue-500/30"
                            >
                            {{ $permission->name }}
                        </label>
                    @endforeach
                </div>

            </div>
        @empty
            <div class="text-sm italic text-zinc-500">
                No permissions available yet.
            </div>
        @endforelse
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3">
        <button
            wire:click="save"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-500
                   text-white text-sm rounded transition"
        >
            Create Role
        </button>

        <a
            href="{{ route('roles.index') }}"
            class="text-sm text-zinc-400 hover:text-zinc-200"
        >
            Cancel
        </a>
    </div>

</div>
