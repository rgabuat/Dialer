<div class="max-w-2xl mx-auto text-fg">

    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Store</h1>
        <p class="text-sm text-fg-muted">Update store details.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-4 bg-surface border border-surface rounded-lg p-6">

        <div>
            <label class="text-sm text-fg-muted">Store Name</label>
            <input wire:model.live="name" type="text"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
            @error('name')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="text-sm text-fg-muted">Address</label>
            <input wire:model.live="address" type="text"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
            @error('address')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="text-sm text-fg-muted">Brand</label>
            <input wire:model.live="brand" type="text"
                class="w-full mt-1 px-3 py-2 bg-surface-2 border border-surface rounded-md" />
            @error('brand')
                <span class="text-xs text-red-400">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-between pt-4">
            <a href="{{ route('stores.index') }}" class="text-sm text-fg-muted hover:text-fg-2">
                ← Back
            </a>

            <button wire:click="save" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 rounded-md text-sm">
                Save Changes
            </button>
        </div>

        {{-- Delete button --}}
        <div class="mt-6">
            <button wire:click="confirmDelete" class="text-sm text-red-400 hover:text-red-300">
                Delete Store
            </button>
        </div>

        {{-- Delete Modal --}}
        @if ($confirmingDelete)
            <div class="fixed inset-0 bg-black/60 flex items-center justify-center z-50">
                <div class="bg-surface border border-surface rounded-lg w-full max-w-sm p-6">

                    <h2 class="text-lg font-semibold mb-2">Delete Store</h2>
                    <p class="text-sm text-fg-muted mb-4">
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
</div>
