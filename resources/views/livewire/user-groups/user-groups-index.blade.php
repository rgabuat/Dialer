<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-lg font-semibold text-white">User Groups</h1>
        @can('user_group.create')
            <a href="{{ route('user-group.create') }}" wire:navigate
                class="inline-flex items-center gap-2 px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New User Group
            </a>
        @endcan
    </div>

    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search user groups..."
            class="w-full sm:w-64 rounded bg-zinc-900 border border-zinc-800
                   px-3 py-2 text-sm text-white focus:outline-none focus:ring focus:ring-blue-500/20"
        >
    </div>

    <div class="overflow-x-auto rounded-lg border border-zinc-800">
        <table class="min-w-full text-sm text-white">
            <thead class="bg-zinc-900 text-zinc-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Campaigns</th>
                    <th class="px-4 py-3 text-left">Users</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-800">
                @forelse ($groups as $group)
                    <tr class="hover:bg-zinc-900/50 transition">
                        <td class="px-4 py-3 font-medium">{{ $group->name }}</td>
                        <td class="px-4 py-3 text-zinc-400 text-xs">{{ $group->campaigns_count }}</td>
                        <td class="px-4 py-3 text-zinc-400 text-xs">{{ $group->users_count }}</td>
                        <td class="px-4 py-3">
                            @if ($group->is_active)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-zinc-700/50 text-zinc-400">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @can('user_group.update')
                                <a href="{{ route('user-group.edit', $group) }}" wire:navigate
                                    class="text-xs text-zinc-400 hover:text-white transition">Edit</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-zinc-500 italic">No user groups found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $groups->links() }}
    </div>
</div>
