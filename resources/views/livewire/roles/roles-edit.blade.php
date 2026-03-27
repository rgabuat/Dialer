<div class="max-w-4xl mx-auto px-6 py-8 space-y-6">

    {{-- Page Header --}}
    <div>
        <h1 class="text-2xl font-black uppercase tracking-wide text-white">
            Edit Role: {{ strtoupper($role->name) }}
        </h1>
        <p class="text-sm text-zinc-400 mt-1 max-w-xl leading-relaxed">
            Modify global permissions for the high-level administrative role. Changes will take effect immediately
            for all associated team members.
        </p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="rounded bg-green-700/30 border border-green-600 px-4 py-2 text-sm text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded bg-red-700/30 border border-red-600 px-4 py-2 text-sm text-red-300">
            {{ session('error') }}
        </div>
    @endif

    {{-- Internal Role Name --}}
    <div class="border border-zinc-700 rounded-lg p-5 bg-zinc-900/40">
        <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-3">
            Internal Role Name
        </label>
        <input
            type="text"
            wire:model.blur="name"
            wire:blur="saveName"
            class="w-full max-w-sm rounded bg-zinc-900 border border-zinc-700
                   px-4 py-2.5 text-white font-semibold text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/50
                   transition"
        >
        @error('name')
            <div class="text-xs text-red-400 mt-1">{{ $message }}</div>
        @enderror
    </div>

    {{-- Module Permissions Table --}}
    <div class="border border-zinc-700 rounded-lg overflow-hidden bg-zinc-900/40">

        {{-- Table Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-700">
            <span class="text-xs font-bold uppercase tracking-widest text-zinc-300">Module Permissions</span>
            <span class="text-xs text-zinc-500">Auto-save: Enabled</span>
        </div>

        {{-- Column Headers --}}
        <div class="grid grid-cols-5 gap-0 px-5 py-3 border-b border-zinc-800">
            <div class="text-xs font-bold uppercase tracking-widest text-zinc-500">Module</div>
            @foreach(['view', 'create', 'update', 'delete'] as $col)
                <div class="text-xs font-bold uppercase tracking-widest text-zinc-500 text-center">
                    {{ $col }}
                </div>
            @endforeach
        </div>

        {{-- Module Rows --}}
        @forelse($moduleActions as $module => $actions)
            <div class="grid grid-cols-5 gap-0 items-center px-5 py-4 border-b border-zinc-800/50 last:border-b-0 hover:bg-zinc-800/20 transition">
                {{-- Module Label --}}
                <div class="text-sm font-medium text-white">
                    {{ ucwords(str_replace(['_', '-', '.'], ' ', $module)) }}
                </div>

                {{-- Action Toggles --}}
                @foreach(['view', 'create', 'update', 'delete'] as $action)
                    @php
                        $permName   = $module . '.' . $action;
                        $permExists = array_key_exists($action, $actions);
                        $enabled    = in_array($permName, $permissions);
                    @endphp
                    <div class="flex justify-center">
                        @if ($permExists)
                            <button
                                wire:click="togglePermission('{{ $permName }}')"
                                title="{{ $enabled ? 'Revoke' : 'Grant' }} {{ $permName }}"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full
                                       border-2 border-transparent transition-colors duration-200 ease-in-out
                                       focus:outline-none focus:ring-2 focus:ring-blue-500/30
                                       {{ $enabled ? 'bg-blue-500' : 'bg-zinc-700' }}"
                            >
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full
                                             bg-white shadow ring-0 transition duration-200 ease-in-out
                                             {{ $enabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        @else
                            <span class="inline-flex h-6 w-11 rounded-full bg-zinc-800 opacity-25 cursor-not-allowed"></span>
                        @endif
                    </div>
                @endforeach
            </div>
        @empty
            <div class="px-5 py-6 text-sm italic text-zinc-500">No permissions defined yet.</div>
        @endforelse

    </div>

    {{-- Add New Permission Attribute --}}
    <div class="relative border border-zinc-700 rounded-lg p-5 bg-zinc-900/40 overflow-hidden">

        <h2 class="text-xs font-bold uppercase tracking-widest text-zinc-300 mb-5">
            Add New Permission Attribute
        </h2>

        <label class="block text-xs font-semibold uppercase tracking-widest text-zinc-500 mb-2">
            Dot-Notation Permission
        </label>

        <div class="flex gap-3">
            <input
                type="text"
                wire:model="newPermission"
                wire:keydown.enter="addPermission"
                placeholder="e.g., users.create.advanced"
                class="flex-1 rounded bg-zinc-900 border border-zinc-700
                       px-4 py-2.5 text-white text-sm placeholder-zinc-600
                       focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500/50
                       transition"
            >
            <button
                wire:click="addPermission"
                class="px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 border border-zinc-600
                       text-white text-sm font-medium rounded transition whitespace-nowrap"
            >
                + Add
            </button>
        </div>

        @error('newPermission')
            <div class="text-xs text-red-400 mt-2">{{ $message }}</div>
        @enderror

        <p class="text-xs text-zinc-600 mt-4 max-w-xl">
            Advanced dot-notation allows for granular control over sub-resources. Ensure the schema is registered
            in the core logic before deployment.
        </p>

    </div>

</div>
