<div class="flex flex-col space-y-4 p-6 h-full stagger-children">

    {{-- Page header --}}
    <div class="flex flex-wrap justify-between items-start gap-4 shrink-0">
        <div>
            <h1 class="font-bold text-fg text-xl">Conversations</h1>
            <p class="mt-0.5 text-fg-muted text-sm">A live-updating list of all conversations.</p>
        </div>
        {{-- Assigned / All tab switcher --}}
        <div class="flex items-center gap-0.5 bg-surface-2 p-0.5 border border-surface rounded-lg">
            <button wire:click="setTab('assigned')"
                class="px-4 py-1.5 rounded-md text-sm font-semibold transition {{ $tab === 'assigned' ? 'bg-surface-3 text-fg shadow-sm' : 'text-fg-muted hover:text-fg hover:bg-surface-3/50' }}">
                Assigned
            </button>
            <button wire:click="setTab('all')"
                class="px-4 py-1.5 rounded-md text-sm font-semibold transition {{ $tab === 'all' ? 'bg-surface-3 text-fg shadow-sm' : 'text-fg-muted hover:text-fg hover:bg-surface-3/50' }}">
                All Conversations
            </button>
        </div>
    </div>

    {{-- Main panel --}}
    <div class="flex flex-col flex-1 bg-surface border border-surface rounded-xl min-h-0 [overflow:clip]">

        {{-- Filter bar + search (single row) --}}
        <div class="flex flex-wrap items-center gap-2 px-5 py-2.5 border-surface border-b shrink-0">
            {{-- Date filter --}}
            <x-date-picker wire-model="filterDate" :value="$filterDate" placeholder="Date" />

            {{-- Channel filter --}}
            <x-select-dropdown wire-model="filterChannel" :value="$filterChannel" placeholder="Channels" :options="[
                ['value' => 'voice', 'label' => 'Voice'],
                ['value' => 'sms', 'label' => 'SMS'],
                ['value' => 'email', 'label' => 'Email'],
                ['value' => 'chat', 'label' => 'Chat'],
            ]" />

            {{-- Status filter --}}
            <x-select-dropdown wire-model="filterStatus" :value="$filterStatus" placeholder="Status" :options="[
                ['value' => 'in_progress', 'label' => 'In Progress'],
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'queued', 'label' => 'Queued'],
                ['value' => 'abandoned', 'label' => 'Abandoned'],
            ]" />

            {{-- Clear filters --}}
            @if ($filterStatus || $filterChannel || $filterDate || $filterQueue)
                <button
                    wire:click="$set('filterStatus',''); $set('filterChannel',''); $set('filterDate',''); $set('filterQueue','')"
                    type="button"
                    class="inline-flex items-center gap-1 text-fg-muted hover:text-fg text-xs transition">
                    <x-heroicon-o-x-mark class="w-3 h-3" />
                    Clear filters
                </button>
            @endif

            {{-- Search + pagination (right side) --}}
            <div class="flex items-center gap-2 ml-auto">
                <div class="relative flex items-center w-48">
                    <x-heroicon-o-magnifying-glass
                        class="left-2.5 absolute w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                    <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Search"
                        class="pl-8 w-full" />
                    @if ($search)
                        <button wire:click="$set('search','')" type="button"
                            class="right-2.5 absolute text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                        </button>
                    @endif
                </div>
                @if ($conversations->hasPages())
                    <span>
                        {{ number_format($conversations->firstItem()) }} –
                        {{ number_format($conversations->lastItem()) }}
                        of
                        <span class="font-medium text-fg">{{ number_format($conversations->total()) }}</span>
                    </span>
                    <div class="flex items-center gap-0.5">
                        @if ($conversations->onFirstPage())
                            <span
                                class="flex justify-center items-center opacity-30 rounded-md w-6 h-6 text-fg-muted cursor-not-allowed">
                                <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                            </span>
                        @else
                            <button wire:click="previousPage" type="button"
                                class="flex justify-center items-center hover:bg-surface-2 rounded-md w-6 h-6 text-fg-muted hover:text-fg transition">
                                <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                            </button>
                        @endif
                        @if ($conversations->hasMorePages())
                            <button wire:click="nextPage" type="button"
                                class="flex justify-center items-center hover:bg-surface-2 rounded-md w-6 h-6 text-fg-muted hover:text-fg transition">
                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                            </button>
                        @else
                            <span
                                class="flex justify-center items-center opacity-30 rounded-md w-6 h-6 text-fg-muted cursor-not-allowed">
                                <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                            </span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 overflow-auto" x-data="{
            _fn: null,
            init() {
                this._fn = () => { this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - 8) + 'px'; };
                this._fn();
                window.addEventListener('resize', this._fn);
            },
            destroy() { window.removeEventListener('resize', this._fn); }
        }">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-fg-muted text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Contact</th>
                        <th class="px-4 py-3 text-left">Channel</th>
                        <th class="px-5 py-3 text-left">Detail</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Duration</th>
                        <th class="px-4 py-3 text-left">Completed By</th>
                        <th class="px-4 py-3 text-right">Created</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conv)
                        @php
                            $initials = $conv->initials;
                            $colors = [
                                'bg-blue-600',
                                'bg-violet-600',
                                'bg-fuchsia-600',
                                'bg-pink-600',
                                'bg-rose-600',
                                'bg-orange-500',
                                'bg-teal-600',
                                'bg-emerald-600',
                                'bg-cyan-600',
                            ];
                            $colorClass = $colors[$conv->id % count($colors)];
                        @endphp
                        <tr wire:key="conv-{{ $conv->id }}"
                            @click="window.location.href = '{{ route('conversations.show', $conv) }}'"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer">

                            {{-- Contact --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3 min-w-[160px]">
                                    <div
                                        class="flex justify-center items-center {{ $colorClass }} rounded-full w-8 h-8 font-bold text-white text-xs shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-fg truncate">
                                            {{ $conv->contact_name ?? 'Unknown' }}</div>
                                        @if ($conv->contact_phone)
                                            <div class="font-mono text-fg-muted text-xs truncate">{{ $conv->contact_phone }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Channel --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    @if ($conv->channel === 'voice')
                                        <div class="flex justify-center items-center bg-blue-500/15 rounded-full w-6 h-6 shrink-0">
                                            <x-heroicon-s-phone class="w-3 h-3 text-blue-400" />
                                        </div>
                                        <span class="font-medium text-fg text-xs capitalize">Voice</span>
                                    @elseif ($conv->channel === 'sms')
                                        <div class="flex justify-center items-center bg-fuchsia-500/15 rounded-full w-6 h-6 shrink-0">
                                            <x-heroicon-s-chat-bubble-left-ellipsis class="w-3 h-3 text-fuchsia-400" />
                                        </div>
                                        <span class="font-medium text-fg text-xs capitalize">SMS</span>
                                    @elseif ($conv->channel === 'email')
                                        <div class="flex justify-center items-center bg-orange-500/15 rounded-full w-6 h-6 shrink-0">
                                            <x-heroicon-s-envelope class="w-3 h-3 text-orange-400" />
                                        </div>
                                        <span class="font-medium text-fg text-xs capitalize">Email</span>
                                    @else
                                        <div class="flex justify-center items-center bg-teal-500/15 rounded-full w-6 h-6 shrink-0">
                                            <x-heroicon-s-chat-bubble-oval-left class="w-3 h-3 text-teal-400" />
                                        </div>
                                        <span class="font-medium text-fg text-xs capitalize">{{ $conv->channel }}</span>
                                    @endif
                                </div>
                            </td>
                            {{-- Detail --}}
                            <td class="px-5 py-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 mb-0.5 text-fg-muted text-xs">
                                        <span class="capitalize">{{ $conv->direction === 'inbound' ? 'Inbound' : 'Outbound' }}</span>
                                        @if ($conv->campaign)
                                            <span class="text-fg-muted/40">·</span>
                                            <span class="max-w-[100px] text-fg-muted/60 truncate">{{ $conv->campaign->name }}</span>
                                        @endif
                                    </div>
                                    @if ($conv->detail_preview)
                                        <div class="max-w-[160px] text-fg text-xs truncate" title="{{ $conv->detail_preview }}">
                                            {{ $conv->detail_preview }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3">
                                @php
                                    $statusMap = [
                                        'in_progress' => [
                                            'label' => 'Call In Progress',
                                            'class' => 'bg-fuchsia-500/15 text-fuchsia-300',
                                        ],
                                        'completed' => [
                                            'label' => 'Complete',
                                            'class' => 'bg-green-500/15 text-accent-green',
                                        ],
                                        'queued' => [
                                            'label' => 'Queued',
                                            'class' => 'bg-yellow-500/15 text-accent-yellow',
                                        ],
                                        'abandoned' => [
                                            'label' => 'Abandoned',
                                            'class' => 'bg-red-500/15 text-accent-red',
                                        ],
                                    ];
                                    $s = $statusMap[$conv->status] ?? [
                                        'label' => ucfirst($conv->status),
                                        'class' => 'bg-surface-2 text-fg-muted',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full font-semibold text-xs {{ $s['class'] }}">
                                    {{ $s['label'] }}
                                </span>
                            </td>

                            {{-- Duration --}}
                            <td class="px-4 py-3 font-mono text-fg-muted text-sm">
                                {{ $conv->duration_label ?? '—' }}
                            </td>

                            {{-- Completed By --}}
                            <td class="px-4 py-3">
                                @if ($conv->completedByAgent)
                                    @php
                                        $cb = $conv->completedByAgent;
                                        $cbInitials = strtoupper(
                                            substr($cb->first_name ?? '', 0, 1) . substr($cb->last_name ?? '', 0, 1),
                                        );
                                        $cbColor = $colors[$cb->id % count($colors)];
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="flex justify-center items-center {{ $cbColor }} rounded-full w-7 h-7 font-bold text-white text-xs shrink-0">
                                            {{ $cbInitials }}
                                        </div>
                                        <div class="min-w-0">
                                            <div class="max-w-[110px] font-medium text-fg text-xs truncate">{{ $cb->first_name }} {{ $cb->last_name }}</div>
                                            @if ($conv->assignedAgent && $conv->assignedAgent->id !== $cb->id)
                                                <div class="max-w-[110px] text-fg-muted text-xs truncate">
                                                    via {{ $conv->assignedAgent->first_name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                @elseif ($conv->assignedAgent)
                                    @php
                                        $agent = $conv->assignedAgent;
                                        $agentInitials = strtoupper(substr($agent->first_name ?? '', 0, 1) . substr($agent->last_name ?? '', 0, 1));
                                        $agentColor = $colors[$agent->id % count($colors)];
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <div class="flex justify-center items-center {{ $agentColor }} rounded-full w-7 h-7 font-bold text-white text-xs shrink-0">
                                            {{ $agentInitials }}
                                        </div>
                                        <span class="max-w-[100px] text-fg-muted text-xs truncate">{{ $agent->first_name }} {{ $agent->last_name }}</span>
                                    </div>
                                @else
                                    <span class="text-fg-muted text-sm">—</span>
                                @endif
                            </td>

                            {{-- Created --}}
                            <td class="px-4 py-3 text-fg-muted text-sm text-right whitespace-nowrap">
                                {{ $conv->started_at ? $conv->started_at->format('g:ia') : $conv->created_at->format('g:ia') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-20 text-center">
                                <x-heroicon-o-chat-bubble-left-right class="mx-auto mb-3 w-10 h-10 text-fg-muted/40" />
                                <p class="text-fg-muted text-sm">No conversations found.</p>
                                @if ($search || $filterStatus || $filterChannel || $filterDate)
                                    <p class="mt-1 text-fg-muted/60 text-xs">Try adjusting your search or filters.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Bottom pagination --}}
        <x-table-pagination :paginator="$conversations" label="conversations" :per-page-options="[25, 50, 100]" />

    </div>

</div>
