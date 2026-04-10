<div class="space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <nav class="flex items-center gap-1.5 text-fg-muted text-xs mb-1">
                <a href="{{ route('campaigns.index') }}" wire:navigate class="hover:text-fg transition">Campaigns</a>
                <span>/</span>
                <a href="{{ route('campaign.edit', $campaign) }}" wire:navigate
                    class="hover:text-fg transition">{{ $campaign->name }}</a>
                <span>/</span>
                <span class="text-fg">Dispositions</span>
            </nav>
            <h1 class="font-bold text-fg text-xl">Dispositions</h1>
        </div>
        <a href="{{ route('campaign.disposition.create', $campaign) }}" wire:navigate
            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-1.5 rounded-lg font-semibold text-white text-sm transition">
            <x-heroicon-o-plus class="w-4 h-4" />
            New Disposition
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 text-accent-green text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-surface">
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Name</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Code</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Category</th>
                    <th class="px-4 py-3 text-center font-semibold text-fg-muted text-xs">DNC</th>
                    <th class="px-4 py-3 text-center font-semibold text-fg-muted text-xs">Callback</th>
                    <th class="px-4 py-3 text-center font-semibold text-fg-muted text-xs">Status</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Scope</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface">
                @forelse ($dispositions as $disp)
                    @php
                        $catClass = match ($disp->category) {
                            'SALE' => 'bg-green-500/15 text-accent-green',
                            'DNC' => 'bg-red-500/15 text-accent-red',
                            'CALLBACK' => 'bg-yellow-500/15 text-accent-yellow',
                            'RETRY' => 'bg-blue-500/15 text-blue-400',
                            default => 'bg-surface-2 text-fg-muted',
                        };
                    @endphp
                    <tr class="hover:bg-surface-2/40 transition" wire:key="disp-{{ $disp->id }}">
                        <td class="px-4 py-3 font-medium text-fg">{{ $disp->name }}</td>
                        <td class="px-4 py-3 font-mono text-fg-muted">{{ $disp->code }}</td>
                        <td class="px-4 py-3">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold text-xs {{ $catClass }}">
                                {{ $disp->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($disp->is_dnc)
                                <x-heroicon-s-no-symbol class="inline w-4 h-4 text-accent-red" />
                            @else
                                <span class="text-fg-muted/30">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($disp->requires_callback)
                                <x-heroicon-s-arrow-path class="inline w-4 h-4 text-accent-yellow" />
                            @else
                                <span class="text-fg-muted/30">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($disp->is_active)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold text-xs bg-green-500/15 text-accent-green">Active</span>
                            @else
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold text-xs bg-surface-2 text-fg-muted">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($disp->campaign_id === null)
                                <span class="text-fg-muted text-xs">Global</span>
                            @else
                                <span class="text-fuchsia-400 text-xs">Campaign</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if ($disp->campaign_id !== null)
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('campaign.disposition.edit', [$campaign, $disp]) }}"
                                        wire:navigate class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                                    <button wire:click="delete({{ $disp->id }})"
                                        wire:confirm="Delete this disposition?"
                                        class="text-accent-red hover:text-red-400 text-xs transition">Delete</button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-fg-muted text-sm">
                            No dispositions yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
