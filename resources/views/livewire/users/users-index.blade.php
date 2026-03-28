<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Accounts</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage user accounts, roles and access.</p>
    </div>

    {{-- USERS TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">Accounts</h2>

            <div class="flex items-center gap-2">
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search accounts..."
                    class="bg-zinc-800 px-3 py-1.5 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 w-44 text-white text-sm placeholder-zinc-500">
                <a href="{{ route('user.create') }}"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    New User
                </a>
            </div>
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
                <thead class="top-0 z-10 sticky bg-zinc-900">
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">ID</th>
                        <th class="px-5 py-3 text-left">Store</th>
                        <th class="px-5 py-3 text-left">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition cursor-pointer"
                            onclick="window.location='{{ route('user.edit', $user->id) }}'">

                            {{-- Name + avatar --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-full
                                                 text-xs font-bold shrink-0 {{ avatarColor($user->email) }}">
                                        {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-white text-sm">{{ $user->first_name }}
                                            {{ $user->last_name }}</div>
                                        <div class="text-zinc-500 text-xs">{{ $user->company ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- ID --}}
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $user->id }}</td>

                            {{-- Store --}}
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $user->store_name ?? '—' }}</td>

                            {{-- Email --}}
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $user->email }}</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-16 text-zinc-500 text-center italic">No accounts found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$users" label="accounts" />

    </div>

</div>
