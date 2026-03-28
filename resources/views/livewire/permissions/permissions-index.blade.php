<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Permissions</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">All system permissions registered across every module.</p>
    </div>

    {{-- Add Permission --}}
    @can('permissions.create')
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Add Permission</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Use dot notation — e.g. <code
                        class="bg-surface-2 px-1 py-0.5 rounded text-fg-3 text-xs">users.create</code></p>
            </div>
            <div class="px-5 py-4">
                <div class="flex gap-2 max-w-md">
                    <input wire:model.defer="name" wire:keydown.enter="create" placeholder="module.action"
                        class="flex-1 bg-surface-2/70 px-3 py-2 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none text-fg text-sm transition placeholder-fg-muted">
                    <button wire:click="create"
                        class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-lg font-medium text-white text-sm transition">
                        Add
                    </button>
                </div>
                @error('name')
                    <p class="mt-2 text-accent-red text-xs">{{ $message }}</p>
                @enderror
            </div>
        </div>
    @endcan

    {{-- Permissions grouped --}}
    @php
        $grouped = $permissions->groupBy(fn($p) => explode('.', $p->name)[0]);
    @endphp

    @forelse($grouped as $module => $perms)
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="flex justify-between items-center px-5 py-3 border-surface border-b">
                <span class="font-semibold text-fg-muted text-xs uppercase tracking-widest">{{ $module }}</span>
                <span class="text-zinc-600 text-xs">{{ $perms->count() }}
                    {{ Str::plural('permission', $perms->count()) }}</span>
            </div>
            <div class="flex flex-wrap gap-2 px-5 py-4">
                @foreach ($perms as $perm)
                    <span
                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 border border-surface-2/60 rounded-md text-fg-3 text-xs">
                        <span class="bg-indigo-400 rounded-full w-1.5 h-1.5 shrink-0"></span>
                        {{ $perm->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @empty
        <div class="bg-surface px-5 py-16 border border-surface rounded-xl text-center">
            <p class="text-zinc-500 text-sm italic">No permissions registered yet.</p>
        </div>
    @endforelse

</div>
