<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Accounts</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage user accounts, roles and access.</p>
    </div>

    {{-- USERS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Accounts</h2>

            <div class="flex items-center gap-2">
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Search accounts..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
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
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">ID</th>
                        <th class="px-5 py-3 text-left">Store</th>
                        <th class="px-5 py-3 text-left">Email</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="hover:bg-hover border-surface border-b transition cursor-pointer"
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
                                        <div class="font-semibold text-fg text-sm">{{ $user->first_name }}
                                            {{ $user->last_name }}</div>
                                        <div class="text-zinc-500 text-xs">{{ $user->company ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- ID --}}
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $user->id }}</td>

                            {{-- Store --}}
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $user->store_name ?? '—' }}</td>

                            {{-- Email --}}
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $user->email }}</td>

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
