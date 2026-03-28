<div class="min-h-screen bg-surface-4 text-fg p-6">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Edit Campaign</h1>
            <p class="text-sm text-fg-muted">Update campaign details.</p>
        </div>
        @can('campaign.delete')
            <button wire:click="confirmDelete"
                class="px-4 py-2 rounded-md bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm font-medium transition">
                Delete Campaign
            </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-500/10 border border-green-500/20 px-4 py-3 text-sm text-green-400">
            {{ session('success') }}
        </div>
    @endif

    {{-- Delete confirmation --}}
    @if ($confirmingDelete)
        <div class="mb-6 rounded-md bg-red-500/10 border border-red-500/20 px-4 py-4 flex items-center justify-between gap-4">
            <p class="text-sm text-red-400">Are you sure you want to delete <strong>{{ $campaign->name }}</strong>? This cannot be undone.</p>
            <div class="flex gap-3 shrink-0">
                <button wire:click="delete"
                    class="px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">
                    Yes, delete
                </button>
                <button wire:click="$set('confirmingDelete', false)"
                    class="px-3 py-1.5 rounded-md bg-zinc-700 hover:bg-zinc-600 text-fg-3 text-sm font-medium transition">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">Campaign information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-fg-muted">Phone Number</label>
                        <input wire:model.defer="phone_number" type="text" placeholder="+1234567890"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('phone_number') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Save Changes
                </button>
                <a href="{{ route('campaigns.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </form>
</div>
