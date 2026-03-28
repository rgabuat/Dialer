<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Stores</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Search and manage all store locations.</p>
    </div>

    {{-- STORES TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">Stores</h2>
            <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search stores..."
                class="bg-zinc-800 px-3 py-1.5 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 w-44 text-white text-sm placeholder-zinc-500">
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm stagger-rows">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Store Name</th>
                        <th class="px-5 py-3 text-left">Address</th>
                        <th class="px-5 py-3 text-left">Brand</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stores as $store)
                        <tr class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition cursor-pointer"
                            onclick="window.location='{{ route('store.edit', $store->id) }}'">
                            <td class="px-5 py-4 font-semibold text-white">{{ $store->name }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $store->address }}</td>
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center bg-zinc-800 px-2.5 py-1 rounded-md font-medium text-zinc-300 text-xs">
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
