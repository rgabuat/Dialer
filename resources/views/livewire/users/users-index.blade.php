<div class="min-h-screen text-zinc-100">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Accounts</h1>
            <p class="text-sm text-zinc-400">Search accounts.</p>
        </div>
            {{-- CREATE USER --}}
            <div>
                <a
                href="{{ route('user.create') }}"
                title="Create User"
                class="px-3 py-2 bg-blue-600 rounded-md text-sm"
            >
                + New User
            </a>
            </div>
    </div>

    {{-- Filters --}}
    <div class="flex items-center justify-between mb-4">
        {{-- <div class="flex items-center gap-2">

            <button
                class="px-3 py-1.5 text-sm rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800">
                Brands
            </button>

            <button
                class="px-3 py-1.5 text-sm rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800">
                Stores
            </button>
        </div> --}}

        {{-- Search --}}
        <div class="relative">
            <input
                wire:model.live.debounce.500ms="search"
                type="text"
                placeholder="Search"
                class="w-64 pl-3 pr-10 py-2 text-sm rounded-md bg-zinc-900 border border-zinc-800 placeholder-zinc-500 focus:outline-none focus:ring-1 focus:ring-zinc-600"
            />
            <span class="absolute right-3 top-2.5 text-zinc-500">⌕</span>
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-800 bg-zinc-900">

        <table class="w-full text-sm">
            <thead class="bg-zinc-900 text-zinc-400 border-b border-zinc-800">
                <tr>
                    <th class="px-4 py-3 text-left font-medium">Name</th>
                    <th class="px-4 py-3 text-left font-medium">ID</th>
                    <th class="px-4 py-3 text-left font-medium">Store</th>
                    <th class="px-4 py-3 text-left font-medium">Contact</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-800">
                @foreach ($users as $user)
                    <tr
                        class="hover:bg-zinc-800/40 transition cursor-pointer"
                        onclick="window.location='{{ route('user.edit', $user->id) }}'"
                    >

                        {{-- Name --}}
                        <td class="px-4 py-3">
                            <div class="font-medium">
                                {{ $user->first_name }} {{ $user->last_name }}
                            </div>
                            <div class="text-xs text-zinc-400">
                                {{ $user->company ?? '—' }}
                            </div>
                        </td>

                        {{-- ID --}}
                        <td class="px-4 py-3 text-zinc-400">
                            {{ $user->id }}
                        </td>

                        {{-- Store --}}
                        <td class="px-4 py-3 text-zinc-300">
                            {{ $user->store_name ?? '—' }}
                        </td>

                        {{-- Contact --}}
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">

                                {{-- Avatar --}}
                                <div
                                    class="w-8 h-8 flex items-center justify-center rounded-full
                                           text-xs font-semibold {{ avatarColor($user->email) }}"
                                >
                                    {{ strtoupper(substr($user->first_name, 0, 1)) }}
                                    {{ strtoupper(substr($user->last_name, 0, 1)) }}
                                </div>

                                <span class="text-zinc-200">
                                    {{ $user->first_name }} {{ $user->last_name }}
                                </span>

                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="flex items-center justify-between px-4 py-3 border-t border-zinc-800 text-sm text-zinc-400">
            <span>
                {{ $users->firstItem() }} – {{ $users->lastItem() }} of {{ $users->total() }}
            </span>

            <div>
                {{ $users->links('pagination::simple-tailwind') }}
            </div>
        </div>

    </div>
</div>
