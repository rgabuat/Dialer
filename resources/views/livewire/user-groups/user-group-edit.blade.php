<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">Edit User Group</h1>
            <p class="text-sm text-zinc-400">Update group details and campaign assignments.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-zinc-400">Group information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-zinc-400">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-zinc-400">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-zinc-400">Active</label>
                    </div>
                </div>
            </div>

            {{-- Campaigns --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Campaigns</h2>
                    <p class="text-sm text-zinc-400">Select which campaigns belong to this group.</p>
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
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                        Save Changes
                    </button>
                    <a href="{{ route('user-groups.index') }}" wire:navigate
                        class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                        Cancel
                    </a>
                </div>
                @can('user_group.delete')
                    <button type="button" wire:click="confirmDelete"
                        class="px-4 py-2 rounded-md bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm font-medium transition">
                        Delete Group
                    </button>
                @endcan
            </div>

        </div>
    </div>
</form>

{{-- DELETE MODAL --}}
@if ($confirmingDelete)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
        <div class="w-full max-w-md rounded-lg bg-surface p-6 shadow-xl">
            <h2 class="text-lg font-semibold text-fg">Delete User Group</h2>
            <p class="mt-2 text-sm text-zinc-400">
                Are you sure you want to delete <span class="font-medium text-fg-2">{{ $group->name }}</span>?
                Users assigned to this group will lose their group assignment.
            </p>
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" wire:click="$set('confirmingDelete', false)"
                    class="rounded-md bg-surface-2 px-4 py-2 text-sm text-zinc-300 hover:bg-zinc-700">
                    Cancel
                </button>
                <button type="button" wire:click="delete"
                    class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-500">
                    Delete
                </button>
            </div>
        </div>
    </div>
@endif
