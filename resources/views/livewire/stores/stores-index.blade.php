<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Stores</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Search and manage all store locations.</p>
    </div>

    {{-- STORES TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Stores</h2>
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search stores..."
                class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data x-init="const update = () => {
            const pg = $el.nextElementSibling;
            $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
        };
        update();
        window.addEventListener('resize', update);
        $cleanup(() => window.removeEventListener('resize', update));">
            <table class="min-w-full text-white text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Store Name</th>
                        <th class="px-5 py-3 text-left">Address</th>
                        <th class="px-5 py-3 text-left">Brand</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stores as $store)
                        <tr class="hover:bg-hover border-surface border-b transition cursor-pointer"
                            onclick="window.location='{{ route('store.edit', $store->id) }}'">
                            <td class="px-5 py-4 font-semibold text-fg">{{ $store->name }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $store->address }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center bg-surface-2 px-2.5 py-1 rounded-md font-medium text-fg-3 text-xs">
                                    {{ $store->brand }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-16 text-zinc-500 text-center italic">No stores found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$stores" label="stores" />

    </div>

</div>
