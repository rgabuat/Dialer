<div class="max-w-2xl mx-auto text-fg">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Lead</h1>
        <p class="text-sm text-zinc-400">Update lead details.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-surface border border-surface rounded-lg p-6 space-y-4">

        {{-- First Name --}}
        <div>
            <label class="text-sm text-zinc-400">First Name</label>
            <input type="text" wire:model.live="first_name"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
            @error('first_name')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Last Name --}}
        <div>
            <label class="text-sm text-zinc-400">Last Name</label>
            <input type="text" wire:model.live="last_name"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
            @error('last_name')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label class="text-sm text-zinc-400">Phone</label>
            <input type="text" wire:model.live="phone"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
        </div>

        {{-- Email --}}
        <div>
            <label class="text-sm text-zinc-400">Email</label>
            <input type="email" wire:model.live="email"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
        </div>

        {{-- Store --}}
        <div>
            <label class="text-sm text-zinc-400">Store</label>
            <select wire:model.live="store_id"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md">
                <option value="">Select Store</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }}</option>
                @endforeach
            </select>
            @error('store_id')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex justify-between items-center pt-6">
            <a href="{{ route('leads.index') }}" class="text-sm text-zinc-400 hover:text-fg-2">
                ← Back
            </a>

            <div class="flex gap-3">
                <button wire:click="confirmDelete" class="text-sm text-red-400 hover:text-red-300">
                    Delete
                </button>

                <button wire:click="save" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-md text-sm">
                    Save Changes
                </button>
            </div>
        </div>

    </div>

    {{-- Delete Modal --}}
    @if ($confirmingDelete)
        <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
            <div class="bg-surface border border-surface rounded-lg w-full max-w-sm p-6">

                <h2 class="text-lg font-semibold mb-2">Delete Lead</h2>
                <p class="text-sm text-zinc-400 mb-4">
                    Are you sure? This action cannot be undone.
                </p>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('confirmingDelete', false)"
                        class="px-3 py-1.5 text-sm bg-surface-2 rounded-md">
                        Cancel
                    </button>

                    <button wire:click="delete" class="px-3 py-1.5 text-sm bg-red-600 rounded-md">
                        Delete
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
