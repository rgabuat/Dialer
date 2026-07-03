<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Campaigns</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage your outbound and inbound call campaigns.</p>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- CAMPAIGNS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Campaigns</h2>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search campaigns..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                @can('campaign.create')
                    <a href="{{ route('campaign.create') }}" wire:navigate
                        class="inline-flex items-center gap-2 bg-fuchsia-600 hover:bg-fuchsia-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        New Campaign
                    </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Dial Mode</th>
                        <th class="px-5 py-3 text-left">In-Groups</th>
                        <th class="px-5 py-3 text-left">CID Rotation</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            $typeColors = [
                                'OUTBOUND' => 'bg-blue-500/10 text-blue-400',
                                'INBOUND'  => 'bg-emerald-500/10 text-accent-green',
                                'BLENDED'  => 'bg-fuchsia-500/10 text-fuchsia-400',
                            ];
                            $modeColors = [
                                'MANUAL'      => 'bg-surface-2 text-fg-muted',
                                'PREVIEW'     => 'bg-yellow-500/10 text-yellow-400',
                                'PROGRESSIVE' => 'bg-indigo-500/10 text-indigo-400',
                                'PREDICTIVE'  => 'bg-orange-500/10 text-orange-400',
                            ];
                        @endphp
                        <tr class="hover:bg-hover border-surface border-b transition">

                            {{-- Name --}}
                            <td class="px-5 py-3.5">
                                <p class="font-semibold text-fg text-sm leading-tight">{{ $campaign->name }}</p>
                                @if ($campaign->description)
                                    <p class="text-fg-muted text-xs mt-0.5 truncate max-w-[200px]">{{ $campaign->description }}</p>
                                @endif
                            </td>

                            {{-- Type --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $typeColors[$campaign->type] ?? 'bg-surface-2 text-fg-muted' }}">
                                    {{ $campaign->type ?? '&mdash;' }}
                                </span>
                            </td>

                            {{-- Dial Mode --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $modeColors[$campaign->dial_mode] ?? 'bg-surface-2 text-fg-muted' }}">
                                    {{ $campaign->dial_mode ?? '&mdash;' }}
                                </span>
                            </td>

                            {{-- In-Groups --}}
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-2 rounded-full text-xs font-bold {{ $campaign->in_groups_count > 0 ? 'bg-indigo-500/10 text-indigo-400' : 'bg-surface-2 text-fg-muted' }}">
                                    {{ $campaign->in_groups_count }}
                                </span>
                            </td>

                            {{-- CID Rotation --}}
                            <td class="px-5 py-3.5">
                                @if ($campaign->cid_rotation)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> On
                                    </span>
                                @else
                                    <span class="text-fg-muted/40 text-xs">&mdash;</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-3.5">
                                @if ($campaign->is_active)
                                    <span class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                        <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Inactive
                                    </span>
                                @endif
                            </td>

                            {{-- 3-dot Actions --}}
                            <td class="px-5 py-3.5 text-right">
                                <div class="relative inline-block" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open" type="button"
                                        class="inline-flex items-center justify-center w-7 h-7 rounded-md text-fg-muted hover:text-fg hover:bg-hover transition">
                                        <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                                    </button>
                                    <div x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute right-0 top-8 z-50 w-36 bg-surface-4 border border-surface-2 rounded-xl shadow-2xl shadow-black/60 py-1"
                                        style="display:none">
                                        <a href="{{ route('campaign.edit', $campaign) }}" wire:navigate
                                            @click="open = false"
                                            class="flex items-center gap-2.5 px-4 py-2 text-sm text-fg hover:bg-hover transition">
                                            <x-heroicon-o-eye class="w-4 h-4 text-fg-muted" />
                                            View
                                        </a>
                                        @can('campaign.update')
                                        <a href="{{ route('campaign.edit', $campaign) }}" wire:navigate
                                            @click="open = false"
                                            class="flex items-center gap-2.5 px-4 py-2 text-sm text-fg hover:bg-hover transition">
                                            <x-heroicon-o-pencil class="w-4 h-4 text-fg-muted" />
                                            Edit
                                        </a>
                                        @endcan
                                        @can('campaign.delete')
                                        <div class="border-t border-surface my-1"></div>
                                        <button type="button"
                                            @click="open = false; $wire.openDeleteModal({{ $campaign->id }})"
                                            class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-accent-red hover:bg-red-500/10 transition">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                            Delete
                                        </button>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-zinc-500 text-center italic">No campaigns found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$campaigns" label="campaigns" />

    </div>

    {{-- Delete confirmation modal --}}
    @if ($deletingCampaignId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm"
            wire:click.self="closeDeleteModal">
            <div class="bg-zinc-900 border border-zinc-700/60 rounded-2xl shadow-2xl w-full max-w-md mx-4">

                <div class="flex items-start gap-3 px-6 pt-6 pb-4">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full bg-red-500/15 shrink-0 mt-0.5">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-accent-red" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <h2 class="font-bold text-fg text-base">Delete Campaign</h2>
                        <p class="text-fg-muted text-sm mt-0.5">This action <strong class="text-fg">cannot be undone</strong>.</p>
                    </div>
                    <button wire:click="closeDeleteModal" type="button" class="text-fg-muted hover:text-fg transition shrink-0 mt-1">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                <div class="px-6 pb-6 space-y-4">

                    <div class="bg-red-500/5 border border-red-500/20 rounded-xl p-4 space-y-2">
                        <p class="font-semibold text-accent-red text-xs uppercase tracking-wide mb-2">What will happen</p>
                        <div class="flex items-start gap-2 text-fg-muted text-xs">
                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" />
                            <span>Campaign <strong class="text-fg">{{ $deletingCampaignName }}</strong> and all its settings will be permanently removed</span>
                        </div>
                        <div class="flex items-start gap-2 text-fg-muted text-xs">
                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" />
                            <span>All call lists, dispositions, and callbacks will be deleted</span>
                        </div>
                        <div class="flex items-start gap-2 text-fg-muted text-xs">
                            <x-heroicon-s-x-circle class="w-3.5 h-3.5 text-red-400 shrink-0 mt-0.5" />
                            <span>Agents currently on this campaign will be signed out immediately</span>
                        </div>
                        <div class="flex items-start gap-2 text-fg-muted text-xs">
                            <x-heroicon-o-information-circle class="w-3.5 h-3.5 text-yellow-400 shrink-0 mt-0.5" />
                            <span>Assigned in-groups and CID group will be <em>unlinked</em>, not deleted</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-fg text-sm font-medium mb-1.5">
                            Type <span class="font-mono bg-surface-2 border border-surface px-1.5 py-0.5 rounded text-sm text-accent-red">{{ $deletingCampaignName }}</span> to confirm
                        </label>
                        <input wire:model.live="deleteInput" type="text"
                            placeholder="Type the campaign name..."
                            autocomplete="off" spellcheck="false"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500">
                        @error('deleteInput')
                            <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-1">
                        <button type="button" wire:click="closeDeleteModal"
                            class="px-4 py-2 rounded-lg bg-surface-2 hover:bg-hover border border-surface text-fg text-sm font-medium transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete"
                            wire:loading.attr="disabled"
                            @disabled($deleteInput !== $expectedDeleteText || $deleteInput === '')
                            @class([
                                'inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition',
                                'bg-red-600 hover:bg-red-500 text-white' => $deleteInput === $expectedDeleteText && $deleteInput !== '',
                                'bg-red-900/30 text-red-500/40 cursor-not-allowed' => $deleteInput !== $expectedDeleteText || $deleteInput === '',
                            ])>
                            <x-heroicon-o-trash class="w-4 h-4" wire:loading.remove wire:target="delete" />
                            <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" wire:loading wire:target="delete" />
                            Delete Campaign
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
