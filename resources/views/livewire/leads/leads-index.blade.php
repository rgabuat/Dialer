<div class="text-zinc-100">

    <div class="flex justify-between mb-6">
        <h1 class="text-2xl font-semibold">Leads</h1>
        <a href="{{ route('lead.create') }}" class="px-3 py-2 bg-blue-600 rounded-md text-sm">
            + New Lead
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900">
        <table class="w-full text-sm">
            <thead class="border-b border-zinc-800 text-zinc-400">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Phone</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Store</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-800">
                @foreach ($leads as $lead)
                    <tr
                        onclick="window.location='{{ route('lead.edit', $lead->id) }}'"
                        class="hover:bg-zinc-800/40 cursor-pointer"
                    >
                        <td class="px-4 py-3 font-medium">
                            {{ $lead->first_name }} {{ $lead->last_name }}
                        </td>
                        <td class="px-4 py-3">{{ $lead->phone ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lead->email ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $lead->store->name }}</td>
                        <td class="px-4 py-3">{{ $lead->creator->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-zinc-800">
            {{ $leads->links('pagination::simple-tailwind') }}
        </div>
    </div>
</div>
