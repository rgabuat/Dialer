<div class="space-y-4 p-6">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Leads</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Browse and manage your leads across all stores.</p>
    </div>

    {{-- LEADS TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">Leads</h2>
            <a href="{{ route('lead.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Lead
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Phone</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Store</th>
                        <th class="px-5 py-3 text-left">Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr onclick="window.location='{{ route('lead.edit', $lead->id) }}'"
                            class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition cursor-pointer">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-white">{{ $lead->first_name }} {{ $lead->last_name }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $lead->phone ?? '—' }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $lead->email ?? '—' }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $lead->store->name }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $lead->creator->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-5 py-3 border-zinc-800 border-t">
            {{ $leads->links('pagination::simple-tailwind') }}
        </div>

    </div>

</div>
