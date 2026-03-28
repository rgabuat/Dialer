<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Create Role</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Define a new role and assign the permissions it should have.</p>
    </div>

    {{-- Role Name --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-surface border-b">
            <h2 class="font-semibold text-fg text-sm">Role Details</h2>
        </div>
        <div class="px-5 py-5">
            <label class="block mb-1.5 font-medium text-fg-muted text-xs">Role Name</label>
            <input type="text" wire:model.defer="name" placeholder="e.g. Admin, Editor"
                class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full max-w-sm text-fg text-sm transition placeholder-fg-muted">
            @error('name')
                <p class="mt-1.5 text-accent-red text-xs">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Permissions table --}}
    @php
        $allPermNames = $groupedPermissions->flatten()->pluck('name')->toArray();
        $actions = $groupedPermissions
            ->flatMap(fn($perms) => $perms->map(fn($p) => explode('.', $p->name)[1] ?? ''))
            ->filter()
            ->unique()
            ->sort()
            ->values();
    @endphp

    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="flex justify-between items-center px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-semibold text-fg text-sm">Assign Permissions</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Choose what this role can access.</p>
            </div>
            <label class="flex items-center gap-2 text-fg-muted text-xs cursor-pointer select-none">
                <span>Select all</span>
                <div class="inline-flex relative items-center">
                    <input type="checkbox" wire:click="toggleAll({{ json_encode($allPermNames) }})"
                        @checked(count(array_diff($allPermNames, $permissions)) === 0 && count($allPermNames) > 0) class="sr-only peer">
                    <div
                        class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                    </div>
                </div>
            </label>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Module</th>
                        @foreach ($actions as $action)
                            <th class="px-5 py-3 text-center">{{ $action }}</th>
                        @endforeach
                        <th class="px-5 py-3 text-zinc-600 text-center">All</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedPermissions as $module => $perms)
                        @php $modulePermNames = $perms->pluck('name')->toArray(); @endphp
                        <tr class="hover:bg-surface-2/30 border-surface/60 last:border-0 border-b transition">
                            <td class="px-5 py-3 font-medium text-zinc-300 capitalize">{{ $module }}</td>
                            @foreach ($actions as $action)
                                @php $perm = $perms->firstWhere('name', $module.'.'.$action); @endphp
                                <td class="px-5 py-3 text-center">
                                    @if ($perm)
                                        <label class="inline-flex relative justify-center items-center cursor-pointer">
                                            <input type="checkbox" wire:model="permissions" value="{{ $perm->name }}"
                                                class="sr-only peer">
                                            <div
                                                class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                            </div>
                                        </label>
                                    @else
                                        <span class="text-zinc-700 text-xs">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-5 py-3 text-center">
                                <label class="inline-flex relative justify-center items-center cursor-pointer">
                                    <input type="checkbox"
                                        wire:click="toggleModule({{ json_encode($modulePermNames) }})"
                                        @checked(count(array_diff($modulePermNames, $permissions)) === 0) class="sr-only peer">
                                    <div
                                        class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                    </div>
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $actions->count() + 2 }}"
                                class="px-5 py-10 text-zinc-500 text-sm text-center italic">No permissions defined yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Add New Permission --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-surface border-b">
            <h2 class="font-semibold text-fg text-sm">Add New Permission</h2>
            <p class="mt-0.5 text-zinc-500 text-xs">Use dot notation — e.g. <code
                    class="bg-surface-2 px-1 py-0.5 rounded text-fg-3 text-xs">users.create</code></p>
        </div>
        <div class="px-5 py-4">
            <div class="flex gap-2 max-w-md">
                <input type="text" wire:model.defer="newPermission" wire:keydown.enter="addPermission"
                    placeholder="module.action"
                    class="flex-1 bg-surface-2/70 px-3 py-2 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none text-fg text-sm transition placeholder-fg-muted">
                <button wire:click="addPermission"
                    class="bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded-lg font-medium text-white text-sm transition">
                    Add
                </button>
            </div>
            @error('newPermission')
                <p class="mt-2 text-accent-red text-xs">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-3 py-1">
        <button wire:click="save"
            class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-lg font-medium text-white text-sm transition">
            Create Role
        </button>
        <a href="{{ route('roles.index') }}" wire:navigate class="text-fg-muted hover:text-fg-2 text-sm transition">
            Cancel
        </a>
    </div>

</div>
