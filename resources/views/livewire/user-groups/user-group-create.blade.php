<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New User Group</h1>
            <p class="text-sm text-fg-muted">Create a user group and assign campaigns to it.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">Group information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
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

            {{-- Campaigns --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Campaigns</h2>
                    <p class="text-sm text-fg-muted">Select which campaigns belong to this group.</p>
                </div>
                <div class="md:col-span-3 space-y-3">
                    @foreach ($campaigns as $campaign)
                        <div class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                id="campaign_{{ $campaign['id'] }}"
                                value="{{ $campaign['id'] }}"
                                wire:model.defer="selectedCampaigns"
                                class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500"
                            />
                            <label for="campaign_{{ $campaign['id'] }}" class="text-sm text-zinc-300">
                                {{ $campaign['name'] }}
                            </label>
                        </div>
                    @endforeach
                    @error('selectedCampaigns')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create User Group
                </button>
                <a href="{{ route('user-groups.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
