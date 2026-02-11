<div class="min-h-screen text-zinc-100">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Stores</h1>
        <p class="text-sm text-zinc-400">Search stores.</p>
    </div>

    {{-- Filters --}}
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
            <button class="px-3 py-1.5 text-sm rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800">
                Brands
            </button>

            <button class="px-3 py-1.5 text-sm rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800">
                Stores
            </button>

            <button
                class="w-8 h-8 flex items-center justify-center rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800">
                +
            </button>
        </div>

        <div class="relative">
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search"
                class="w-64 pl-3 pr-10 py-2 text-sm rounded-md bg-zinc-900 border border-zinc-800 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-600" />
            <span class="absolute right-3 top-2.5 text-zinc-500">⌕</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900">

        <table class="w-full text-sm">
            <thead class="bg-zinc-900 text-zinc-400 border-b border-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Store Name</th>
                    <th class="px-4 py-3 text-left font-medium">Address</th>
                    <th class="px-4 py-3 text-left font-medium">Brand</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-800">
                @foreach ($stores as $store)
                    <tr class="hover:bg-zinc-800/40 transition cursor-pointer"
                        onclick="window.location='{{ route('store.edit', $store->id) }}'">
                        {{-- Store Name --}}
                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $store->name }}
                            </div>
                        </td>


                        {{-- Address --}}
                        <td class="px-4 py-3 text-zinc-300">
                            {{ $store->address }}
                        </td>

                        {{-- Brand --}}
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs rounded-md
                                         bg-zinc-800 text-zinc-200">
                                {{ $store->brand }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-4 py-3 border-t border-zinc-800 text-sm text-zinc-400">
            <span>
                {{ $stores->firstItem() }} – {{ $stores->lastItem() }} of {{ $stores->total() }}
            </span>

            <div>
                {{ $stores->links('pagination::simple-tailwind') }}
            </div>
        </div>
    </div>
</div>
