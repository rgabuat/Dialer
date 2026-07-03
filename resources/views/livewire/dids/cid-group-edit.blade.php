<div class="space-y-5 p-6 stagger-children">

    {{-- ── Breadcrumb ── --}}
    <div class="flex items-center gap-1.5 text-fg-muted text-xs">
        <a href="{{ route('cid-groups.index') }}" wire:navigate class="hover:text-fg transition">CID Groups</a>
        <x-heroicon-o-chevron-right class="w-3 h-3 shrink-0" />
        <span class="text-fg truncate">{{ $cidGroup->name }}</span>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-2.5 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5">

        {{-- ── Inline header / group details ── --}}
        <div class="bg-surface border border-surface rounded-xl p-5">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                {{-- Left: name + description + active --}}
                <div class="flex-1 min-w-0 space-y-3">
                    <div>
                        <input wire:model.defer="name" type="text"
                            placeholder="Group name"
                            class="w-full bg-transparent text-fg text-xl font-bold placeholder:text-fg-muted/40 focus:outline-none border-b border-transparent focus:border-fuchsia-500 pb-0.5 transition">
                        @error('name') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <input wire:model.defer="description" type="text"
                            placeholder="Add a description…"
                            class="w-full bg-transparent text-fg-muted text-sm placeholder:text-fg-muted/30 focus:outline-none focus:text-fg border-b border-transparent focus:border-surface pb-0.5 transition">
                        @error('description') <p class="text-accent-red text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    {{-- Active toggle --}}
                    <label class="inline-flex items-center gap-3 cursor-pointer select-none">
                        <div class="relative">
                            <input type="checkbox" wire:model.defer="is_active" class="sr-only peer">
                            <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-fuchsia-500 transition-colors"></div>
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                        </div>
                        <span class="text-fg text-sm font-medium">Active</span>
                    </label>
                </div>

                {{-- Right: actions --}}
                <div class="flex items-center gap-2 shrink-0">
                    @if ($confirmingDelete)
                        <span class="text-fg-muted text-xs hidden sm:inline">Are you sure?</span>
                        <button wire:click="delete" type="button"
                            class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-500 px-3 py-1.5 rounded-lg text-white text-sm font-medium transition">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            Delete
                        </button>
                        <button wire:click="$set('confirmingDelete', false)" type="button"
                            class="px-3 py-1.5 rounded-lg bg-surface-2 hover:bg-hover border border-surface text-fg text-sm font-medium transition">
                            Cancel
                        </button>
                    @else
                        <button wire:click="confirmDelete" type="button"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-2 hover:bg-red-500/10 border border-surface hover:border-red-500/30 text-fg-muted hover:text-accent-red text-sm font-medium transition">
                            <x-heroicon-o-trash class="w-3.5 h-3.5" />
                            Delete
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg bg-fuchsia-600 hover:bg-fuchsia-500 text-white text-sm font-medium transition">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            Save
                        </button>
                    @endif
                </div>
            </div>

            {{-- Bound campaign inline badge --}}
            @php $boundCampaign = $cidGroup->campaign; @endphp
            @if ($boundCampaign)
                <div class="mt-4 pt-4 border-t border-surface flex items-center gap-3">
                    <x-heroicon-s-megaphone class="w-3.5 h-3.5 text-indigo-400 shrink-0" />
                    <span class="text-fg-muted text-xs">Bound to campaign</span>
                    <a href="{{ route('campaign.edit', $boundCampaign) }}" wire:navigate
                        class="inline-flex items-center gap-1.5 bg-indigo-500/10 hover:bg-indigo-500/20 px-2.5 py-1 rounded-md text-indigo-400 text-xs font-medium transition">
                        {{ $boundCampaign->name }}
                        <x-heroicon-o-arrow-top-right-on-square class="w-3 h-3" />
                    </a>
                    <span class="text-fg-muted/40 text-xs ml-auto hidden sm:inline">Numbers in this group are exclusive to that campaign's outbound calls</span>
                </div>
            @endif
        </div>

        {{-- ── CID Numbers picker ── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden"
            x-data="{
                search: '',
                get filtered() {
                    const q = this.search.toLowerCase();
                    return q === ''
                        ? null
                        : q;
                }
            }">

            {{-- Header --}}
            <div class="flex items-center gap-3 px-4 py-3 border-b border-surface bg-surface-2">
                <x-heroicon-s-phone class="w-3.5 h-3.5 text-emerald-400 shrink-0" />
                <span class="font-semibold text-fg text-sm">CID Numbers</span>
                <span class="ml-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full bg-fuchsia-500/15 text-fuchsia-400 text-xs font-bold tabular-nums">
                    {{ count($selectedCidIds) }}
                </span>
                {{-- Search --}}
                <div class="relative ml-auto">
                    <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                    <input x-model="search" type="text" placeholder="Search numbers..."
                        class="bg-surface-2 pl-8 pr-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                </div>
            </div>

            @error('selectedCidIds')
                <div class="flex items-center gap-2 bg-red-500/10 border-b border-red-500/20 px-4 py-2.5 text-accent-red text-xs">
                    <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0" />
                    {{ $message }}
                </div>
            @enderror

            @if ($allCidNumbers->isEmpty())
                <div class="px-5 py-8 text-center">
                    <x-heroicon-o-phone class="w-8 h-8 text-fg-muted/30 mx-auto mb-2" />
                    <p class="text-fg-muted text-sm">No CID numbers imported yet.</p>
                    <a href="{{ route('cid-numbers.index') }}" wire:navigate class="text-fuchsia-400 hover:underline text-sm mt-1 inline-block">Import numbers</a>
                </div>
            @else
                {{-- Sorted: selected first, then available, then locked --}}
                @php
                    $inGroup   = $allCidNumbers->filter(fn($c) => in_array((string)$c->id, $selectedCidIds));
                    $available = $allCidNumbers->filter(fn($c) => !in_array((string)$c->id, $selectedCidIds) && !($c->cid_group_id && $c->cid_group_id !== $cidGroup->id));
                    $locked    = $allCidNumbers->filter(fn($c) => $c->cid_group_id && $c->cid_group_id !== $cidGroup->id);
                @endphp

                <div class="divide-y divide-surface">

                    {{-- In this group --}}
                    @foreach ($inGroup as $cid)
                        <label for="cid-{{ $cid->id }}"
                            x-show="search === '' || '{{ strtolower($cid->phone_number) }}'.includes(search.toLowerCase()) || '{{ strtolower($cid->friendly_name ?? '') }}'.includes(search.toLowerCase())"
                            class="flex items-center gap-3 px-4 py-2.5 bg-fuchsia-500/5 hover:bg-fuchsia-500/10 transition cursor-pointer">
                            <div class="relative shrink-0 pointer-events-none">
                                <input type="checkbox" id="cid-{{ $cid->id }}" wire:model="selectedCidIds" value="{{ $cid->id }}" class="sr-only peer">
                                <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-fuchsia-500 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-mono text-fg text-sm font-semibold leading-none">{{ $cid->phone_number }}</p>
                                @if ($cid->friendly_name)
                                    <p class="text-fg-muted text-xs mt-0.5 truncate">{{ $cid->friendly_name }}</p>
                                @endif
                            </div>
                            @if (!$cid->is_active)
                                <span class="text-[10px] font-medium text-fg-muted bg-surface border border-surface px-1.5 py-0.5 rounded shrink-0">Inactive</span>
                            @endif
                        </label>
                    @endforeach

                    {{-- Available --}}
                    @foreach ($available as $cid)
                        <label for="cid-{{ $cid->id }}"
                            x-show="search === '' || '{{ strtolower($cid->phone_number) }}'.includes(search.toLowerCase()) || '{{ strtolower($cid->friendly_name ?? '') }}'.includes(search.toLowerCase())"
                            class="flex items-center gap-3 px-4 py-2.5 hover:bg-hover transition cursor-pointer">
                            <div class="relative shrink-0 pointer-events-none">
                                <input type="checkbox" id="cid-{{ $cid->id }}" wire:model="selectedCidIds" value="{{ $cid->id }}" class="sr-only peer">
                                <div class="w-9 h-5 rounded-full bg-zinc-600 peer-checked:bg-fuchsia-500 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-mono text-fg text-sm font-semibold leading-none">{{ $cid->phone_number }}</p>
                                @if ($cid->friendly_name)
                                    <p class="text-fg-muted text-xs mt-0.5 truncate">{{ $cid->friendly_name }}</p>
                                @endif
                            </div>
                            @if (!$cid->is_active)
                                <span class="text-[10px] font-medium text-fg-muted bg-surface border border-surface px-1.5 py-0.5 rounded shrink-0">Inactive</span>
                            @endif
                        </label>
                    @endforeach

                    {{-- Locked to other group --}}
                    @foreach ($locked as $cid)
                        <div x-show="search === '' || '{{ strtolower($cid->phone_number) }}'.includes(search.toLowerCase()) || '{{ strtolower($cid->friendly_name ?? '') }}'.includes(search.toLowerCase())"
                            class="flex items-center gap-3 px-4 py-2.5 opacity-40">
                            <div class="relative shrink-0">
                                <div class="w-9 h-5 rounded-full bg-zinc-700"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white/50 shadow"></div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-mono text-fg text-sm font-semibold leading-none">{{ $cid->phone_number }}</p>
                                @if ($cid->friendly_name)
                                    <p class="text-fg-muted text-xs mt-0.5 truncate">{{ $cid->friendly_name }}</p>
                                @endif
                            </div>
                            <span class="text-[10px] font-medium text-yellow-400 bg-yellow-500/10 border border-yellow-500/20 px-1.5 py-0.5 rounded shrink-0">Other group</span>
                        </div>
                    @endforeach

                </div>

                {{-- Footer --}}
                <div class="px-4 py-2.5 border-t border-surface bg-surface-2 flex items-center gap-3 text-xs text-fg-muted">
                    <span><span class="text-fg font-semibold">{{ count($selectedCidIds) }}</span> selected</span>
                    <span class="text-fg-muted/30">·</span>
                    <span>{{ $available->count() }} available</span>
                    @if ($locked->count())
                        <span class="text-fg-muted/30">·</span>
                        <span>{{ $locked->count() }} locked to other groups</span>
                    @endif
                    <button type="button" x-show="search !== ''" x-on:click="search = ''"
                        class="ml-auto text-fuchsia-400 hover:underline hidden">Clear search</button>
                </div>
            @endif
        </div>

    </form>

</div>
