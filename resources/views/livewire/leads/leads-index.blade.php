<div class="flex flex-col space-y-4 p-6 h-full">

    {{-- Page header --}}
    <div class="flex flex-wrap justify-between items-start gap-4 shrink-0">
        <div>
            <h1 class="font-bold text-fg text-xl">Leads</h1>
            <p class="mt-0.5 text-fg-muted text-sm">All created and imported leads.</p>
        </div>
        <a href="{{ route('lead.create') }}"
            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition">
            <x-heroicon-o-plus class="w-3.5 h-3.5" />
            New Lead
        </a>
    </div>

    {{-- Main panel --}}
    <div class="flex flex-col flex-1 bg-surface border border-surface rounded-xl min-h-0 [overflow:clip]">

        {{-- Filter bar --}}
        <div class="flex flex-wrap items-center gap-2 px-5 py-2.5 border-surface border-b shrink-0">

            {{-- Type filter --}}
            <x-select-dropdown wire-model="filterType" :value="$filterType" placeholder="Type" :options="[
                ['value' => 'quote',       'label' => 'Self Storage Quote'],
                ['value' => 'reservation', 'label' => 'Reservation'],
                ['value' => 'waitlist',    'label' => 'Waitlist'],
                ['value' => 'rental',      'label' => 'Rental'],
            ]" />

            {{-- Store filter --}}
            <x-select-dropdown wire-model="filterStore" :value="$filterStore" placeholder="Store"
                :options="$stores->map(fn($s) => ['value' => $s->id, 'label' => $s->name])->values()->all()" />

            {{-- User filter --}}
            <x-select-dropdown wire-model="filterUser" :value="$filterUser" placeholder="Agent"
                :options="$users->map(fn($u) => ['value' => $u->id, 'label' => $u->name])->values()->all()" />

            {{-- Clear filters --}}
            @if ($search || $filterType || $filterStore || $filterUser || !empty($filterStatus))
                <button wire:click="resetFilters" type="button"
                    class="inline-flex items-center gap-1 text-fg-muted hover:text-fg text-xs transition">
                    <x-heroicon-o-x-mark class="w-3 h-3" />
                    Clear filters
                </button>
            @endif

            {{-- Search --}}
            <div class="relative flex items-center ml-auto w-56">
                <x-heroicon-o-magnifying-glass
                    class="left-2.5 absolute w-3.5 h-3.5 text-fg-muted pointer-events-none" />
                <x-input wire:model.live.debounce.300ms="search" type="text" placeholder="Search leads…"
                    class="pl-8 w-full" />
                @if ($search)
                    <button wire:click="$set('search','')" type="button"
                        class="right-2.5 absolute text-fg-muted hover:text-fg transition">
                        <x-heroicon-o-x-mark class="w-3.5 h-3.5" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Table --}}
        <div class="flex-1 min-h-0 overflow-auto">
            <table class="min-w-full text-fg text-sm">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-fg-muted text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Contact</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Store</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Stage</th>
                        <th class="px-4 py-3 text-left">Value</th>
                        <th class="px-4 py-3 text-left">Target Close</th>
                        <th class="px-4 py-3 text-left">Age</th>
                        <th class="px-4 py-3 text-left">Last Actioned</th>
                        <th class="px-4 py-3 text-left">Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        @php
                            $typeLabel = match($lead->lead_type) {
                                'quote'       => 'Self Storage Quote',
                                'reservation' => 'Reservation',
                                'waitlist'    => 'Waitlist',
                                'rental'      => 'Rental',
                                default       => ucfirst($lead->lead_type ?? 'Lead'),
                            };
                            $totalValue = collect($lead->selected_units ?? [])
                                ->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
                            $stage = ucfirst(str_replace('_', ' ', $lead->pipeline_stage ?? 'interested'));

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
                                'open', 'new' => 'bg-yellow-400/20 text-yellow-300',
                                'won'         => 'bg-green-400/20 text-green-300',
                                'lost'        => 'bg-red-400/20 text-red-300',
                                default       => 'bg-surface-2 text-fg-muted',
                            };
                        @endphp
                        <tr wire:key="lead-{{ $lead->id }}"
                            onclick="window.location.href='{{ route('lead.edit', $lead->id) }}'"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer">

                            {{-- Contact --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5 min-w-[160px]">
                                    <div class="flex items-center justify-center {{ $contactColor }} rounded-full w-8 h-8 font-bold text-xs shrink-0">
                                        {{ $contactInitials ?: '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-fg text-xs truncate">{{ $lead->first_name }} {{ $lead->last_name }}</p>
                                        <p class="text-fg-muted text-xs truncate">{{ $lead->phone ?? $lead->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Type --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-semibold text-fg text-xs">{{ $typeLabel }}</p>
                                <p class="text-fg-muted text-xs">{{ $lead->source ?? 'CSRScape' }}</p>
                            </td>

                            {{-- Store --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($lead->store)
                                    <p class="font-medium text-fuchsia-400 text-xs">{{ $lead->store->name }}</p>
                                    <p class="text-fg-muted text-xs">{{ $lead->store->brand ?? '' }}</p>
                                @else
                                    <span class="text-fg-muted text-xs">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full font-semibold text-xs {{ $statusClass }}">
                                    {{ ucfirst(strtolower($lead->status ?? 'New')) }}
                                </span>
                            </td>

                            {{-- Stage --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="text-fg-muted text-xs">{{ $stage }}</span>
                            </td>

                            {{-- Value --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($totalValue > 0)
                                    <span class="font-semibold text-fg text-xs">${{ number_format($totalValue) }}</span>
                                @else
                                    <span class="text-fg-muted text-xs">—</span>
                                @endif
                            </td>

                            {{-- Target Close --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($targetDate)
                                    <p class="font-medium text-xs {{ $targetFuture ? 'text-green-400' : 'text-fg-muted' }}">
                                        {{ $targetDate->diffForHumans(null, true, false, 1) }}
                                    </p>
                                    <p class="text-fg-muted text-xs">{{ $targetDate->format('j M Y') }}</p>
                                @else
                                    <span class="text-fg-muted text-xs">—</span>
                                @endif
                            </td>

                            {{-- Age --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="font-medium text-fg text-xs">{{ $lead->created_at->diffForHumans(null, true, false, 1) }}</p>
                                <p class="text-fg-muted text-xs">{{ $lead->created_at->format('d M Y') }}</p>
                            </td>

                            {{-- Last Actioned --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex items-center justify-center {{ $actorColor }} rounded-full w-6 h-6 font-bold text-xs shrink-0">
                                        {{ $actorInitials }}
                                    </div>
                                    <span class="text-fg-muted text-xs">{{ ($lead->last_called_at ?? $lead->updated_at)->format('d M Y') }}</span>
                                </div>
                            </td>

                            {{-- Created By --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div class="flex items-center justify-center {{ $creatorColor }} rounded-full w-6 h-6 font-bold text-xs shrink-0">
                                        {{ $creatorInitials }}
                                    </div>
                                    <span class="text-fg-muted text-xs">{{ $lead->creator?->name ?? '—' }}</span>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-20 text-center">
                                <x-heroicon-o-document-text class="mx-auto mb-3 w-10 h-10 text-fg-muted/40" />
                                <p class="text-fg-muted text-sm">No leads found.</p>
                                @if ($search || $filterType || $filterStore || $filterUser)
                                    <p class="mt-1 text-fg-muted/60 text-xs">Try adjusting your search or filters.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$leads" label="leads" :per-page-options="[25, 50, 100]" />

    </div>

</div>
