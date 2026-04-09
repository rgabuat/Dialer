<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="font-bold text-fg text-xl">Scheduled Callbacks</h1>
    </div>

    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-surface-2 border-b border-surface">
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Lead</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Phone</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Campaign</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Scheduled</th>
                    <th class="px-4 py-3 text-left font-semibold text-fg-muted text-xs">Notes</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface">
                @forelse ($callbacks as $cb)
                    @php
                        $isPast = $cb->scheduled_at->isPast();
                        $leadName = trim(optional($cb->lead)->first_name . ' ' . optional($cb->lead)->last_name);
                    @endphp
                    <tr class="hover:bg-surface-2/40 transition" wire:key="cb-{{ $cb->id }}">
                        <td class="px-4 py-3 text-fg">{{ $leadName ?: '—' }}</td>
                        <td class="px-4 py-3 font-mono text-fg-muted text-xs">
                            {{ optional($cb->lead)->phone_number ?? '—' }}</td>
                        <td class="px-4 py-3 text-fg-muted text-xs">{{ optional($cb->campaign)->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs {{ $isPast ? 'text-accent-red font-semibold' : 'text-fg' }}">
                                {{ $cb->scheduled_at->format('j M Y g:ia') }}
                                @if ($isPast)
                                    <span class="ml-1 text-accent-red">(overdue)</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-4 py-3 text-fg-muted text-xs max-w-xs truncate">{{ $cb->notes ?? '—' }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="complete({{ $cb->id }})"
                                    class="text-accent-green hover:text-green-400 text-xs transition">Done</button>
                                <button wire:click="cancel({{ $cb->id }})" wire:confirm="Cancel this callback?"
                                    class="text-accent-red hover:text-red-400 text-xs transition">Cancel</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-fg-muted text-sm">
                            No pending callbacks.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $callbacks->links() }}
</div>
