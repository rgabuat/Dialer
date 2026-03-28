<div class="max-w-2xl mx-auto text-fg">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Create Lead</h1>
        <p class="text-sm text-zinc-400">Add a new lead.</p>
    </div>

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
        <div class="flex justify-between pt-4">
            <a href="{{ route('leads.index') }}" class="text-sm text-zinc-400 hover:text-fg-2">
                ← Back
            </a>

            <button wire:click="save" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-md text-sm">
                Create Lead
            </button>
        </div>

    </div>
</div>
