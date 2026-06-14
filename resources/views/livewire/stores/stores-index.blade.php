<div class="flex flex-col space-y-4 p-6 h-full">

    {{-- Page header --}}
    <div class="flex flex-wrap justify-between items-start gap-4 shrink-0">
        <div>
            <h1 class="font-bold text-fg text-xl">Stores</h1>
            <p class="mt-0.5 text-fg-muted text-sm">Search and manage all store locations.</p>
        </div>
    </div>

    {{-- Main panel --}}
    <div class="flex flex-col flex-1 bg-surface border border-surface rounded-xl min-h-0 [overflow:clip]">

        {{-- Filter bar --}}
        <div class="flex flex-wrap items-center gap-2 px-5 py-2.5 border-surface border-b shrink-0">

            {{-- Search --}}
            <div class="relative flex items-center ml-auto w-56">
                <x-heroicon-o-magnifying-glass
                    class="left-2.5 absolute w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Search stores…"
                    class="pl-8 w-full" />
                @if ($search)
                    <button wire:click="$set('search','')" type="button"
                        class="right-2.5 absolute text-fg-muted hover:text-fg transition">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 min-h-0 overflow-auto">
            <table class="min-w-full text-fg text-sm">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-fg-muted text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Store Name</th>
                        <th class="px-5 py-3 text-left">Brand</th>
                        <th class="px-5 py-3 text-left">Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stores as $store)
                        <tr wire:key="store-{{ $store->id }}"
                            onclick="window.location.href='{{ route('store.edit', $store->id) }}'"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer">

                            {{-- Store Name --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex items-center justify-center bg-fuchsia-500/20 rounded-lg w-8 h-8 shrink-0">
                                        <x-heroicon-o-building-storefront class="w-4 h-4 text-fuchsia-400" />
                                    </div>
                                    <span class="font-semibold text-fg text-sm">{{ $store->name }}</span>
                                </div>
                            </td>

                            {{-- Brand --}}
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center bg-surface-2 px-2.5 py-1 rounded-md font-medium text-fg text-xs">
                                    {{ $store->brand }}
                                </span>
                            </td>

                            {{-- Address --}}
                            <td class="px-5 py-3 text-fg-muted text-sm">
                                {{ $store->address }}
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-20 text-center">
                                <x-heroicon-o-building-storefront class="mx-auto mb-3 w-10 h-10 text-fg-muted/40" />
                                <p class="text-fg-muted text-sm">No stores found.</p>
                                @if ($search)
                                    <p class="mt-1 text-fg-muted/60 text-xs">Try adjusting your search.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$stores" label="stores" :per-page-options="[25, 50, 100]" />

    </div>

</div>
