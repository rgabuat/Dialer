<div class="space-y-4 p-6 stagger-children">

    {{-- Page header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-bold text-fg text-xl">CID Groups</h1>
            <p class="mt-0.5 text-zinc-500 text-sm">Create named groups of Caller ID numbers. Bind a group to a campaign
                to enable per-campaign CID rotation — numbers in a group are exclusive to that campaign.</p>
        </div>
        <button wire:click="openCreate" type="button"
            class="inline-flex items-center gap-2 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-2 rounded-lg text-white text-sm font-medium transition">
            <x-heroicon-o-plus class="w-4 h-4" />
            New CID Group
        </button>
    </div>

    {{-- Flash success --}}
    @if (session('success') || $successMessage)
        <div class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ session('success') ?? $successMessage }}
        </div>
    @endif

    {{-- Groups table --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
        <div class="px-5 py-4 border-b border-surface bg-surface-2">
            <h2 class="font-bold text-fg text-base">All CID Groups</h2>
            <p class="text-fg-muted text-xs mt-0.5">{{ $groups->count() }} group{{ $groups->count() !== 1 ? 's' : '' }}</p>
        </div>

        @if ($groups->isEmpty())
            <p class="px-5 py-8 text-fg-muted text-sm italic text-center">No CID groups yet. Create one to start binding numbers to campaigns.</p>
        @else
            <div class="overflow-auto">
                <table class="min-w-full text-fg text-sm">
                    <thead class="top-0 z-10 sticky bg-surface">
                        <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left">Name</th>
                            <th class="px-5 py-3 text-center">Numbers</th>
                            <th class="px-5 py-3 text-left">Bound Campaign</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groups as $group)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="px-5 py-3 font-semibold text-fg">
                                    {{ $group->name }}
                                    @if ($group->description)
                                        <p class="text-fg-muted text-xs font-normal mt-0.5">{{ $group->description }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-fuchsia-500/10 text-fuchsia-400 text-xs font-bold">
                                        {{ $group->cid_numbers_count }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    @if ($group->campaign)
                                        <a href="{{ route('campaign.edit', $group->campaign) }}" wire:navigate
                                            class="inline-flex items-center gap-1.5 bg-indigo-500/10 px-2.5 py-1 rounded text-indigo-400 text-xs font-medium hover:bg-indigo-500/20 transition">
                                            <x-heroicon-s-megaphone class="w-3 h-3" />
                                            {{ $group->campaign->name }}
                                        </a>
                                    @else
                                        <span class="text-fg-muted/50 text-xs italic">Unbound</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">
                                    @if ($group->is_active)
                                        <span class="inline-flex items-center gap-1.5 bg-green-500/10 px-2 py-0.5 rounded text-accent-green text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 bg-surface-2 px-2 py-0.5 rounded text-fg-muted text-xs font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <a href="{{ route('cid-group.edit', $group) }}" wire:navigate
                                        class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-hover border border-surface px-3 py-1.5 rounded-md text-fg text-xs font-medium transition">
                                        <x-heroicon-o-pencil class="w-3.5 h-3.5" />
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Create modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm"
            wire:click.self="$set('showCreateModal', false)">
            <div class="bg-surface border border-surface rounded-xl shadow-2xl w-full max-w-md mx-4">
                <div class="flex items-center justify-between px-5 py-4 border-b border-surface">
                    <h2 class="font-bold text-fg text-base">New CID Group</h2>
                    <button wire:click="$set('showCreateModal', false)" type="button"
                        class="text-fg-muted hover:text-fg transition">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>
                <form wire:submit.prevent="create" class="p-5 space-y-4">
                    <div>
                        <label class="block mb-1.5 font-semibold text-fg text-xs">Group Name <span class="text-accent-red">*</span></label>
                        <input wire:model.defer="newName" type="text" placeholder="e.g. Sales Outbound Pool"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                        @error('newName') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 font-semibold text-fg text-xs">Description</label>
                        <textarea wire:model.defer="newDescription" rows="2"
                            placeholder="Optional description..."
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-fuchsia-500"></textarea>
                        @error('newDescription') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer w-fit">
                        <input wire:model.defer="newIsActive" type="checkbox"
                            class="w-4 h-4 rounded text-fuchsia-600 border-surface-2 focus:ring-fuchsia-500 focus:ring-offset-0">
                        <span class="text-fg text-sm font-medium">Active</span>
                    </label>
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" wire:click="$set('showCreateModal', false)"
                            class="px-4 py-2 rounded-lg bg-surface-2 hover:bg-hover border border-surface text-fg text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-fuchsia-600 hover:bg-fuchsia-500 text-white text-sm font-medium transition">
                            Create Group
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
