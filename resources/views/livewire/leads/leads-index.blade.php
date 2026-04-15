<div class="flex flex-col h-full">

    {{-- Page header --}}
    <div class="px-6 pt-5 pb-3 shrink-0">
        <h1 class="font-bold text-fg text-xl leading-tight">Leads</h1>
        <p class="text-fg-muted text-sm mt-0.5">All created and imported leads.</p>
    </div>

    {{-- Filter bar --}}
    <div class="flex items-center gap-2 px-6 py-2 border-b border-surface shrink-0 flex-wrap">

        {{-- Brands dropdown placeholder --}}
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-surface bg-surface-2 hover:bg-surface-3 text-fg text-xs font-semibold transition">
            Brands <x-heroicon-o-chevron-down class="w-3 h-3 text-fg-muted" />
        </button>

        {{-- Users dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-surface bg-surface-2 hover:bg-surface-3 text-fg text-xs font-semibold transition">
                Users <x-heroicon-o-chevron-down class="w-3 h-3 text-fg-muted" />
            </button>
            <div x-show="open" @click.outside="open = false"
                class="absolute left-0 top-full mt-1 z-30 bg-surface-2 border border-surface rounded-xl shadow-xl w-52 py-1">
                @foreach($users as $u)
                <button wire:click="$set('filterUser', '{{ $filterUser == $u->id ? '' : $u->id }}')" @click="open = false"
                    class="w-full flex items-center gap-2 px-3 py-2 text-xs text-fg hover:bg-surface-3 transition">
                    @if($filterUser == $u->id)<x-heroicon-s-check class="w-3.5 h-3.5 text-fuchsia-400 shrink-0" />@else<span class="w-3.5 shrink-0"></span>@endif
                    {{ $u->name }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Category placeholder --}}
        <button class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-surface bg-surface-2 hover:bg-surface-3 text-fg text-xs font-semibold transition">
            Category <x-heroicon-o-chevron-down class="w-3 h-3 text-fg-muted" />
        </button>

        {{-- Type dropdown --}}
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                @class(['inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-semibold transition',
                    'border-fuchsia-500/40 bg-fuchsia-500/10 text-fuchsia-300' => $filterType !== '',
                    'border-surface bg-surface-2 hover:bg-surface-3 text-fg' => $filterType === ''])>
                Type <x-heroicon-o-chevron-down class="w-3 h-3 opacity-60" />
            </button>
            <div x-show="open" @click.outside="open = false"
                class="absolute left-0 top-full mt-1 z-30 bg-surface-2 border border-surface rounded-xl shadow-xl w-56 py-1">
                @foreach(['quote' => 'Self Storage Quote', 'reservation' => 'Reservation', 'waitlist' => 'Waitlist', 'rental' => 'Rental'] as $val => $label)
                <button wire:click="$set('filterType', '{{ $filterType === $val ? '' : $val }}')" @click="open = false"
                    class="w-full flex items-center gap-2 px-3 py-2 text-xs hover:bg-surface-3 transition {{ $filterType === $val ? 'text-fuchsia-300' : 'text-fg' }}">
                    @if($filterType === $val)<x-heroicon-s-check class="w-3.5 h-3.5 text-fuchsia-400 shrink-0" />@else<span class="w-3.5 h-3.5 shrink-0"></span>@endif
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Active status filter chip --}}
        @if(!empty($filterStatus))
        <div class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-fuchsia-500/40 bg-fuchsia-500/10 text-fuchsia-300 text-xs font-semibold">
            <span>Status: {{ implode(', ', array_map('ucfirst', $filterStatus)) }}</span>
            <button wire:click="resetFilters" class="ml-1 hover:text-white transition">
                <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
            </button>
        </div>
        @endif

        <button class="w-6 h-6 flex items-center justify-center rounded-full border border-surface bg-surface-2 hover:bg-surface-3 text-fg-muted hover:text-fg transition">
            <x-heroicon-o-plus class="w-3.5 h-3.5" />
        </button>

        @if($search || $filterType || $filterStore || $filterUser)
        <button wire:click="resetFilters" class="text-xs text-fg-muted hover:text-fg transition underline underline-offset-2">Reset</button>
        @endif

        {{-- Search --}}
        <div class="ml-auto flex items-center gap-3">
            <div class="relative">
                <x-heroicon-o-magnifying-glass class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search leads…"
                    class="bg-surface-2 border border-surface rounded-lg pl-8 pr-3 py-1.5 text-xs text-fg placeholder-fg-muted focus:outline-none focus:border-fuchsia-500 w-52 transition" />
            </div>

            {{-- Count + pagination arrows --}}
            <span class="text-fg-muted text-xs shrink-0">
                {{ $leads->firstItem() ?? 0 }}–{{ $leads->lastItem() ?? 0 }} of {{ number_format($leads->total()) }}
            </span>
            <div class="flex items-center gap-1">
                <button wire:click="previousPage"
                    class="w-6 h-6 flex items-center justify-center rounded border border-surface hover:bg-surface-2 text-fg-muted transition {{ $leads->onFirstPage() ? 'opacity-30 pointer-events-none' : '' }}">
                    <x-heroicon-o-chevron-left class="w-3.5 h-3.5" />
                </button>
                <button wire:click="nextPage"
                    class="w-6 h-6 flex items-center justify-center rounded border border-surface hover:bg-surface-2 text-fg-muted transition {{ !$leads->hasMorePages() ? 'opacity-30 pointer-events-none' : '' }}">
                    <x-heroicon-o-chevron-right class="w-3.5 h-3.5" />
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="flex-1 overflow-auto">
        <table class="w-full text-sm min-w-[1200px]">
            <thead class="sticky top-0 z-10 bg-surface border-b border-surface">
                <tr class="text-fg-muted text-xs font-semibold">
                    <th class="px-4 py-3 text-left">Contact</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Store</th>
                    <th class="flex-1 px-4 py-3 text-left"></th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-left">Stage</th>
                    <th class="px-4 py-3 text-left">Value</th>
                    <th class="px-4 py-3 text-left">Target Close</th>
                    <th class="px-4 py-3 text-left">Age</th>
                    <th class="px-4 py-3 text-left">Last Actioned</th>
                    <th class="px-4 py-3 text-left">Created By</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-surface">
                @forelse ($leads as $lead)
                @php
                    $typeLabel = match($lead->lead_type) {
                        'quote'       => 'Self Storage Quote',
                        'reservation' => 'Self Storage Reservation',
                        'waitlist'    => 'Self Storage Waitlist',
                        'rental'      => 'Self Storage Rental',
                        default       => ucfirst($lead->lead_type ?? 'Lead'),
                    };
                    $totalValue = collect($lead->selected_units ?? [])
                        ->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
                    $stage = ucfirst($lead->pipeline_stage ?? 'interested');

                    $avatarColors = [
                        'bg-fuchsia-500/30 text-fuchsia-200',
                        'bg-blue-500/30 text-blue-200',
                        'bg-green-500/30 text-green-200',
                        'bg-yellow-500/30 text-yellow-200',
                        'bg-orange-500/30 text-orange-200',
                        'bg-cyan-500/30 text-cyan-200',
                        'bg-rose-500/30 text-rose-200',
                        'bg-violet-500/30 text-violet-200',
                        'bg-teal-500/30 text-teal-200',
                        'bg-pink-500/30 text-pink-200',
                    ];
                    $contactColor = $avatarColors[$lead->id % count($avatarColors)];
                    $creatorColor = $avatarColors[($lead->created_by ?? 0) % count($avatarColors)];
                    $actorColor   = $avatarColors[($lead->last_actioned_by ?? $lead->id) % count($avatarColors)];

                    $contactInitials = strtoupper(substr($lead->first_name ?? '?', 0, 1)) . strtoupper(substr($lead->last_name ?? '', 0, 1));
                    $creatorInitials = strtoupper(substr($lead->creator?->name ?? '?', 0, 1)) . strtoupper(substr(strstr($lead->creator?->name ?? '', ' ') ?: '', 1, 1));
                    $actorInitials   = strtoupper(substr($lead->lastActionedBy?->name ?? $lead->creator?->name ?? '?', 0, 1)) . strtoupper(substr(strstr($lead->lastActionedBy?->name ?? $lead->creator?->name ?? '', ' ') ?: '', 1, 1));

                    $targetDate   = $lead->move_in_date ?? $lead->expires_at;
                    $targetFuture = $targetDate && $targetDate->isFuture();

                    $statusClass = match(strtolower($lead->status ?? '')) {
                        'open', 'new' => 'bg-yellow-400/20 text-yellow-300 border border-yellow-400/30',
                        'won'         => 'bg-green-400/20 text-green-300 border border-green-400/30',
                        'lost'        => 'bg-red-400/20 text-red-300 border border-red-400/30',
                        default       => 'bg-surface-3 text-fg-muted border border-surface',
                    };
                @endphp
                <tr onclick="window.location='{{ route('lead.edit', $lead->id) }}'"
                    class="hover:bg-surface-2/50 transition cursor-pointer">

                    {{-- Contact --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full {{ $contactColor }} flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $contactInitials ?: '?' }}
                            </div>
                            <div>
                                <p class="text-fg text-xs font-semibold leading-tight">{{ $lead->first_name }} {{ $lead->last_name }}</p>
                                <p class="text-fg-muted text-xs">{{ $lead->store?->brand ?? $lead->store?->name ?? '—' }}</p>
                            </div>
                        </div>
                    </td>

                    {{-- Type --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <p class="text-fg text-xs font-semibold leading-tight">{{ $typeLabel }}</p>
                        <p class="text-fg-muted text-xs">{{ $lead->source ?? 'CSRScape' }}</p>
                    </td>

                    {{-- Store --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($lead->store)
                        <p class="text-fuchsia-400 text-xs font-medium leading-tight">{{ $lead->store->name }}</p>
                        <p class="text-fg-muted text-xs">{{ implode(', ', array_filter([$lead->store->city, $lead->store->state])) }}</p>
                        @else
                        <span class="text-fg-muted text-xs">—</span>
                        @endif
                    </td>

                    {{-- Spacer --}}
                    <td class="w-full"></td>

                    {{-- Status --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                            {{ ucfirst(strtolower($lead->status ?? 'New')) }}
                        </span>
                    </td>

                    {{-- Stage --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="text-fg-muted text-xs">{{ $stage }}</span>
                    </td>

                    {{-- Value --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($totalValue > 0)
                        <span class="text-fg text-xs font-semibold">${{ number_format($totalValue) }}</span>
                        @else
                        <span class="text-fg-muted text-xs">—</span>
                        @endif
                    </td>

                    {{-- Target Close --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($targetDate)
                        <p class="text-xs font-medium {{ $targetFuture ? 'text-green-400' : 'text-fg-muted' }}">
                            in {{ $targetDate->diffForHumans(null, true, false, 1) }}
                        </p>
                        <p class="text-fg-muted text-xs">{{ $targetDate->format('j M Y') }}</p>
                        @else
                        <span class="text-fg-muted text-xs">—</span>
                        @endif
                    </td>

                    {{-- Age --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <p class="text-fg text-xs font-medium">{{ $lead->created_at->diffForHumans(null, true, false, 1) }}</p>
                        <p class="text-fg-muted text-xs">{{ $lead->created_at->format('d M Y') }}</p>
                    </td>

                    {{-- Last Actioned --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 rounded-full {{ $actorColor }} flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $actorInitials }}
                            </div>
                            <span class="text-fg-muted text-xs">{{ ($lead->last_called_at ?? $lead->updated_at)->format('d M Y') }}</span>
                        </div>
                    </td>

                    {{-- Created By --}}
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-1.5">
                            <div class="w-6 h-6 rounded-full {{ $creatorColor }} flex items-center justify-center text-xs font-bold shrink-0">
                                {{ $creatorInitials }}
                            </div>
                            <span class="text-fg-muted text-xs">{{ $lead->creator?->name ?? '—' }}</span>
                        </div>
                    </td>

                </tr>
                @empty
                <tr>
                    <td colspan="11" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <x-heroicon-o-document-text class="w-10 h-10 text-fg-muted/20" />
                            <p class="text-fg-muted text-sm font-medium">No leads found</p>
                            @if($search || $filterType || $filterStore || $filterUser)
                            <button wire:click="resetFilters" class="text-fuchsia-400 hover:text-fuchsia-300 text-xs underline transition">Clear filters</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
