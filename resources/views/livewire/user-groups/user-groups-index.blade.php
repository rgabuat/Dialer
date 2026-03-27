<div class="p-6">

    {{-- USER GROUPS TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">User Groups</h2>

            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search groups..."
                    class="bg-zinc-800 px-3 py-1.5 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 w-44 text-white text-sm placeholder-zinc-500">
                @can('user_group.create')
                    <a href="{{ route('user-group.create') }}" wire:navigate
                        class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        New Group
                    </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Campaigns</th>
                        <th class="px-5 py-3 text-left">Users</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition">
                            <td class="px-5 py-4 font-semibold text-white">{{ $group->name }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $group->campaigns_count }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">{{ $group->users_count }}</td>
                            <td class="px-5 py-4">
                                @if ($group->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-green-400 text-xs uppercase tracking-wide">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-zinc-700/50 px-2.5 py-1 rounded-md font-bold text-zinc-400 text-xs uppercase tracking-wide">
                                        <span class="bg-zinc-500 rounded-full w-1.5 h-1.5"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @can('user_group.update')
                                    <a href="{{ route('user-group.edit', $group) }}" wire:navigate
                                        class="text-zinc-400 hover:text-white text-xs transition">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No user groups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($groups->hasPages())
            <div class="flex justify-between items-center px-5 py-3 border-zinc-800 border-t">
                <span class="text-zinc-500 text-xs">
                    Showing
                    <span class="font-medium text-white">{{ $groups->firstItem() }} – {{ $groups->lastItem() }}</span>
                    of
                    <span class="font-medium text-white">{{ number_format($groups->total()) }}</span>
                    groups
                </span>
                {{ $groups->links() }}
            </div>
        @else
            <div class="px-5 py-3 border-zinc-800 border-t">
                <span class="text-zinc-500 text-xs">
                    Showing <span class="font-medium text-white">{{ $groups->total() }}</span> groups
                </span>
            </div>
        @endif

    </div>

</div>
