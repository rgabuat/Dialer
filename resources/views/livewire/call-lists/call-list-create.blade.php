<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-fg-muted mb-1">
                <a href="{{ route('campaign.edit', $campaign) }}" wire:navigate
                    class="hover:text-fg transition">{{ $campaign->name }}</a>
                <span>/</span>
                <a href="{{ route('campaign.lists', $campaign) }}" wire:navigate class="hover:text-fg transition">Call
                    Lists</a>
                <span>/</span>
                <span>New</span>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">New Call List</h1>
            <p class="text-sm text-fg-muted">Create a lead list for this campaign.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">List configuration.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">List Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Timezone</label>
                            <input wire:model.defer="timezone" type="text" placeholder="UTC"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Sort Order</label>
                            <input wire:model.defer="sort_order" type="number" min="0"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create List
                </button>
                <a href="{{ route('campaign.lists', $campaign) }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
