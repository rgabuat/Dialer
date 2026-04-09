<div class="space-y-4 p-6 stagger-children">

    <div class="flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-fg-muted mb-1">
                <a href="{{ route('campaign.edit', $campaign) }}" wire:navigate
                    class="hover:text-fg transition">{{ $campaign->name }}</a>
                <span>/</span>
                <span>Call Lists</span>
            </div>
            <h1 class="font-bold text-fg text-xl">Call Lists</h1>
            <p class="mt-0.5 text-zinc-500 text-sm">Manage lead lists for this campaign.</p>
        </div>
        @can('call-list.create')
            <a href="{{ route('campaign.list.create', $campaign) }}" wire:navigate
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New List
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="rounded-md bg-green-500/10 border border-green-500/20 px-4 py-3 text-sm text-accent-green">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
        <div class="overflow-auto">
            <table class="min-w-full text-fg text-sm">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Timezone</th>
                        <th class="px-5 py-3 text-right">Leads</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($lists as $list)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            <td class="px-5 py-4 font-semibold text-fg">{{ $list->name }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $list->timezone }}</td>
                            <td class="px-5 py-4 text-right font-mono text-fg-muted">
                                {{ number_format($list->leads_count) }}</td>
                            <td class="px-5 py-4">
                                @if ($list->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                        <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @can('call-list.update')
                                    <a href="{{ route('campaign.list.edit', [$campaign, $list]) }}" wire:navigate
                                        class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No call lists yet.
                                Create your first list to start adding leads.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
