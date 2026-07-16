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
                ['value' => 'quote', 'label' => 'Self Storage Quote'],
                ['value' => 'reservation', 'label' => 'Reservation'],
                ['value' => 'waitlist', 'label' => 'Waitlist'],
                ['value' => 'rental', 'label' => 'Rental'],
            ]" />

            {{-- Store filter --}}
            <x-select-dropdown wire-model="filterStore" :value="$filterStore" placeholder="Store" :options="$stores->map(fn($s) => ['value' => $s->id, 'label' => $s->name])->values()->all()" />

            {{-- User filter --}}
            <x-select-dropdown wire-model="filterUser" :value="$filterUser" placeholder="Agent" :options="$users->map(fn($u) => ['value' => $u->id, 'label' => $u->name])->values()->all()" />

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
                        <th class="px-4 py-3 text-left">Details</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Target Date</th>
                        <th class="px-4 py-3 text-left">Created</th>
                        <th class="px-4 py-3 text-left">Agent</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        @php
                            $typeLabel = match ($lead->lead_type) {
                                'quote' => 'Quote',
                                'reservation' => 'Reservation',
                                'waitlist' => 'Waitlist',
                                'rental' => 'Rental',
                                default => ucfirst($lead->lead_type ?? 'Lead'),
                            };
                            $typeColor = match ($lead->lead_type) {
                                'quote' => 'bg-blue-500/10 text-blue-400',
                                'reservation' => 'bg-indigo-500/10 text-indigo-400',
                                'waitlist' => 'bg-orange-500/10 text-orange-400',
                                'rental' => 'bg-green-500/10 text-green-400',
                                default => 'bg-surface-2 text-fg-muted',
                            };
                            $totalValue = collect($lead->selected_units ?? [])->sum(
                                fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1),
                            );
                            $stage = match ($lead->pipeline_stage ?? 'interested') {
                                'interested' => ['label' => 'Interested', 'class' => 'text-yellow-400'],
                                'converted' => ['label' => 'Converted', 'class' => 'text-green-400'],
                                'expired' => ['label' => 'Expired', 'class' => 'text-fg-muted'],
                                'no_longer_interested' => ['label' => 'Not Interested', 'class' => 'text-red-400'],
                                default => [
                                    'label' => ucfirst(str_replace('_', ' ', $lead->pipeline_stage ?? '')),
                                    'class' => 'text-fg-muted',
                                ],
                            };
                            $avatarColors = [
                                'bg-fuchsia-500/30 text-fuchsia-200',
                                'bg-blue-500/30 text-blue-200',
                                'bg-green-500/30 text-green-200',
                                'bg-yellow-500/30 text-yellow-200',
                                'bg-orange-500/30 text-orange-200',
                                'bg-cyan-500/30 text-cyan-200',
                                'bg-rose-500/30 text-rose-200',
                                'bg-violet-500/30 text-violet-200',
                            ];
                            $contactColor = $avatarColors[$lead->id % count($avatarColors)];
                            $creatorColor = $avatarColors[($lead->created_by ?? 0) % count($avatarColors)];
                            $contactInitials =
                                strtoupper(substr($lead->first_name ?? '?', 0, 1)) .
                                strtoupper(substr($lead->last_name ?? '', 0, 1));
                            $creatorInitials =
                                strtoupper(substr($lead->creator?->name ?? '?', 0, 1)) .
                                strtoupper(substr(strstr($lead->creator?->name ?? '', ' ') ?: '', 1, 1));
                            $targetDate = $lead->move_in_date ?? $lead->expires_at;
                            $targetFuture = $targetDate && $targetDate->isFuture();
                            $statusClass = match (strtolower($lead->status ?? '')) {
                                'open', 'new' => 'bg-yellow-400/15 text-yellow-300 border-yellow-400/20',
                                'won' => 'bg-green-400/15 text-green-300 border-green-400/20',
                                'lost' => 'bg-red-400/15 text-red-300 border-red-400/20',
                                default => 'bg-surface-2 text-fg-muted border-surface',
                            };
                            $dynData = collect($lead->dynamic_data ?? [])
                                ->filter(fn($v) => $v !== '' && $v !== null)
                                ->take(3);
                        @endphp
                        <tr wire:key="lead-{{ $lead->id }}"
                            onclick="window.location.href='{{ route('lead.edit', $lead->id) }}'"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer group">

                            {{-- Contact --}}
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        class="flex items-center justify-center {{ $contactColor }} rounded-full w-8 h-8 font-bold text-xs shrink-0">
                                        {{ $contactInitials ?: '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-fg text-sm leading-tight">
                                            {{ trim(($lead->first_name ?? '') . ' ' . ($lead->last_name ?? '')) ?: '—' }}
                                        </p>
                                        <p class="text-fg-muted text-xs truncate max-w-[160px]">
                                            {{ $lead->phone ?? ($lead->email ?? '—') }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Details --}}
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ $typeColor }}">
                                        {{ $typeLabel }}
                                    </span>
                                    @if ($lead->store)
                                        <span
                                            class="text-xs text-fuchsia-400 font-medium">{{ $lead->store->name }}</span>
                                    @endif
                                    @if ($totalValue > 0)
                                        <span
                                            class="text-xs font-semibold text-fg">${{ number_format($totalValue) }}</span>
                                    @endif
                                </div>
                                @if ($dynData->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach ($dynData as $dk => $dv)
                                            <span
                                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-surface-2 border border-surface text-[10px] text-fg-muted">
                                                <span
                                                    class="text-fg-muted/60">{{ ucfirst(str_replace('_', ' ', $dk)) }}:</span>
                                                <span class="text-fg">{{ Str::limit((string) $dv, 20) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded border font-semibold text-xs {{ $statusClass }}">
                                    {{ ucfirst(strtolower($lead->status ?? 'New')) }}
                                </span>
                                <p class="mt-1 text-xs {{ $stage['class'] }}">{{ $stage['label'] }}</p>
                            </td>

                            {{-- Target Date --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if ($targetDate)
                                    <p
                                        class="font-medium text-xs {{ $targetFuture ? 'text-emerald-400' : 'text-fg-muted line-through' }}">
                                        {{ $targetDate->format('j M Y') }}
                                    </p>
                                    <p class="text-fg-muted text-xs">{{ $targetDate->diffForHumans() }}</p>
                                @else
                                    <span class="text-fg-muted text-xs">—</span>
                                @endif
                            </td>

                            {{-- Created --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-fg text-xs font-medium">{{ $lead->created_at->format('j M Y') }}</p>
                                <p class="text-fg-muted text-xs">{{ $lead->created_at->diffForHumans() }}</p>
                            </td>

                            {{-- Agent --}}
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1.5">
                                    <div
                                        class="flex items-center justify-center {{ $creatorColor }} rounded-full w-6 h-6 font-bold text-xs shrink-0">
                                        {{ $creatorInitials ?: '?' }}
                                    </div>
                                    <span class="text-fg-muted text-xs">{{ $lead->creator?->name ?? '—' }}</span>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-20 text-center">
                                <svg class="mx-auto mb-3 w-10 h-10 text-fg-muted/25" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                                <p class="font-medium text-fg-muted text-sm">No leads found</p>
                                @if ($search || $filterType || $filterStore || $filterUser)
                                    <p class="mt-1 text-fg-muted/60 text-xs">Try clearing your filters.</p>
                                @else
                                    <p class="mt-1 text-fg-muted/60 text-xs">Create a new lead to get started.</p>
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
