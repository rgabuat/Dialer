@php
    $ld = $lead;
    $ldTypeLabel = match($ld->lead_type) {
        'quote'       => 'Quote',
        'reservation' => 'Reservation',
        'waitlist'    => 'Waitlist',
        'rental'      => 'Rental',
        default       => ucfirst($ld->lead_type ?? 'Lead'),
    };
    $ldUnits      = collect($ld->selected_units ?? []);
    $ldMoveInRent = $ldUnits->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
    $ldInsurance  = $ld->property_protection ? (float) $ld->property_protection : 0;
    $ldAdminFee   = 29;
    $ldTotal      = $ldMoveInRent + $ldInsurance + $ldAdminFee;
    $ldName       = trim(($ld->first_name ?? '') . ' ' . ($ld->last_name ?? ''));
    $ldInitials   = strtoupper(substr($ld->first_name ?? '?', 0, 1)) . strtoupper(substr($ld->last_name ?? '', 0, 1));
    $statusClass  = match(strtolower($ld->status ?? '')) {
        'open', 'new' => 'bg-yellow-400/20 text-yellow-300 border border-yellow-400/30',
        'won'         => 'bg-green-400/20 text-green-300 border border-green-400/30',
        'lost'        => 'bg-red-400/20 text-red-300 border border-red-400/30',
        default       => 'bg-surface-3 text-fg-muted border border-surface',
    };
@endphp

<div class="flex flex-col h-full">

    {{-- Top bar --}}
    <div class="flex items-center justify-between px-5 py-3 border-b border-surface shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('leads.index') }}"
                class="flex items-center justify-center w-7 h-7 rounded hover:bg-surface-2 text-fg-muted hover:text-fg transition">
                <x-heroicon-o-arrow-left class="w-4 h-4" />
            </a>
            <div>
                <p class="font-bold text-fg text-sm leading-tight">Sales Lead</p>
                <p class="text-fg-muted text-xs">A Self Storage {{ $ldTypeLabel }} Sales lead.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            @if($ld->conversation)
            <a href="{{ route('conversations.show', $ld->conversation) }}"
                class="inline-flex items-center gap-2 bg-fuchsia-600 hover:bg-fuchsia-500 px-4 py-1.5 rounded-lg font-semibold text-white text-sm transition">
                Continue Conversation
            </a>
            @endif
            <button class="flex items-center justify-center w-7 h-7 rounded hover:bg-surface-2 text-fg-muted transition">
                <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Sub-header --}}
    <div class="px-5 py-2.5 border-b border-surface shrink-0 flex items-center gap-2" x-data="{ stageOpen: false }">

        {{-- Stage icon button + flydown --}}
        <div class="relative">
            <button @click="stageOpen = !stageOpen"
                class="flex items-center justify-center w-7 h-7 rounded hover:bg-surface-2 text-fg-muted hover:text-fg transition"
                title="Set pipeline stage">
                <x-heroicon-o-arrows-right-left class="w-4 h-4" />
            </button>

            <div x-show="stageOpen" @click.outside="stageOpen = false" x-transition
                class="absolute left-0 top-full mt-1 w-72 bg-surface-2 border border-surface rounded-xl shadow-xl z-50 overflow-hidden">
                <div class="px-4 py-3 border-b border-surface">
                    <p class="font-bold text-fg text-sm">Pipeline Stage</p>
                    <p class="text-fg-muted text-xs mt-0.5">Set the lead's stage on the current pipeline.</p>
                </div>
                <div class="divide-y divide-surface">
                    @php
                        $stages = [
                            ['key' => 'interested',          'label' => 'Interested',          'desc' => 'The contact is interested.',                  'dot' => 'bg-yellow-400'],
                            ['key' => 'converted',           'label' => 'Converted',            'desc' => 'This lead has been converted into a sale.',    'dot' => 'bg-green-400'],
                            ['key' => 'expired',             'label' => 'Expired',              'desc' => 'This lead has expired.',                       'dot' => 'bg-surface-4'],
                            ['key' => 'no_longer_interested','label' => 'No Longer Interested', 'desc' => 'The contact is no longer interested.',         'dot' => 'bg-surface-4'],
                        ];
                    @endphp
                    @foreach($stages as $s)
                    <button wire:click="setPipelineStage('{{ $s['key'] }}')" @click="stageOpen = false"
                        class="w-full flex items-center gap-3 px-4 py-3 hover:bg-surface-3 transition text-left {{ $lead->pipeline_stage === $s['key'] ? 'bg-surface-3' : '' }}">
                        <span class="w-3 h-3 rounded-full {{ $s['dot'] }} shrink-0"></span>
                        <div>
                            <p class="text-fg text-sm font-medium">{{ $s['label'] }}</p>
                            <p class="text-fg-muted text-xs">{{ $s['desc'] }}</p>
                        </div>
                        @if($lead->pipeline_stage === $s['key'])
                        <x-heroicon-o-check class="w-4 h-4 text-fuchsia-400 ml-auto shrink-0" />
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- 3-column layout --}}
    <div class="flex flex-1 min-h-0 overflow-hidden">

        {{-- LEFT: Overview sidebar --}}
        <div class="w-48 shrink-0 border-r border-surface overflow-y-auto">
            <div class="px-4 py-3 border-b border-surface">
                <p class="font-bold text-fg text-sm leading-tight">Overview</p>
                <p class="text-fg-muted text-xs mt-0.5">Overview of this lead.</p>
            </div>
            <div class="divide-y divide-surface text-xs">
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Category</span>
                    <span class="text-fuchsia-400 font-medium text-right">Sales</span>
                </div>
                <div class="grid grid-cols-2 gap-1 items-center px-4 py-2.5">
                    <span class="text-fg-muted">Type</span>
                    <span class="flex justify-end">
                        <span class="px-2 py-0.5 rounded-full font-semibold bg-fuchsia-500/20 text-fuchsia-300">{{ $ldTypeLabel }}</span>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-1 items-center px-4 py-2.5">
                    <span class="text-fg-muted">Status</span>
                    <span class="flex justify-end">
                        <span class="px-2 py-0.5 rounded-full font-semibold {{ $statusClass }}">{{ ucfirst(strtolower($ld->status ?? 'New')) }}</span>
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Pipeline</span>
                    <span class="text-fuchsia-400 font-medium text-right">Sales</span>
                </div>
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Pipeline Stage</span>
                    <span class="text-fuchsia-400 font-medium text-right">{{ ucfirst($ld->pipeline_stage ?? 'Interested') }}</span>
                </div>
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Created</span>
                    <span class="text-fg font-medium text-right leading-snug">{{ $ld->created_at->format('d M Y, g:ia') }}</span>
                </div>
                @if($ld->store)
                <div class="px-4 py-2.5">
                    <span class="text-fg-muted block mb-0.5">Store</span>
                    <a href="#" class="text-fuchsia-400 font-medium leading-tight hover:underline">{{ $ld->store->name }}</a>
                    <p class="text-fg-muted mt-0.5">{{ implode(', ', array_filter([$ld->store->city ?? '', $ld->store->state ?? ''])) }}</p>
                </div>
                @endif
            </div>

            <div class="px-4 py-3 flex items-center gap-2.5 border-t border-surface">
                <div class="w-8 h-8 rounded-full bg-fuchsia-500/30 text-fuchsia-200 flex items-center justify-center text-sm font-bold shrink-0">
                    {{ $ldInitials ?: '?' }}
                </div>
                <div>
                    <p class="text-fg text-xs font-semibold leading-tight">{{ $ldName ?: '—' }}</p>
                    @if($ld->store)<p class="text-fg-muted text-xs">{{ $ld->store->name }}</p>@endif
                </div>
            </div>

            <div class="divide-y divide-surface text-xs">
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Account</span>
                    <a href="#" class="text-fuchsia-400 font-medium text-right hover:underline">{{ $ldName ?: '—' }}</a>
                </div>
                <div class="px-4 py-2.5">
                    <span class="text-fg-muted block">Phone</span>
                    <span class="text-fg font-medium">{{ $ld->phone ?? '—' }}</span>
                    @if($ld->phone)<p class="text-fg-muted">Mobile</p>@endif
                </div>
                @if($ld->address)
                <div class="px-4 py-2.5">
                    <span class="text-fg-muted block">Address</span>
                    <span class="text-fg font-medium">{{ $ld->address }}</span>
                </div>
                @endif
            </div>

            <div class="divide-y divide-surface text-xs border-t border-surface">
                @if($ld->source)
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Source</span>
                    <span class="text-fg font-medium text-right">{{ $ld->source }}</span>
                </div>
                @endif
                @if($ld->move_in_date)
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Move In Date</span>
                    <span class="text-fuchsia-400 font-medium text-right">{{ $ld->move_in_date->format('j M Y') }}</span>
                </div>
                @endif
                @if($ld->duration)
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Duration</span>
                    <span class="text-fg font-medium text-right">{{ $ld->duration }}</span>
                </div>
                @endif
                @if($ld->reason_for_storage)
                <div class="grid grid-cols-2 gap-1 px-4 py-2.5">
                    <span class="text-fg-muted">Reason For</span>
                    <span class="text-fg font-medium text-right">{{ $ld->reason_for_storage }}</span>
                </div>
                @endif
                @if($ld->types_of_items)
                <div class="px-4 py-2.5">
                    <span class="text-fg-muted block">Types Of Items</span>
                    <span class="text-fuchsia-400 font-medium">{{ $ld->types_of_items }}</span>
                </div>
                @endif
            </div>
        </div>

        {{-- MIDDLE: Activity --}}
        <div class="flex-1 border-r border-surface overflow-y-auto">
            <div class="px-5 py-3 border-b border-surface">
                <p class="font-bold text-fg text-sm">Activity</p>
                <p class="text-fg-muted text-xs">A history of activity on this lead.</p>
            </div>
            <div class="p-6 space-y-6">
                @php
                    $cInitials = strtoupper(substr($ld->creator?->name ?? '?', 0, 1)) . strtoupper(substr(strstr($ld->creator?->name ?? '', ' ') ?: '', 1, 1));

                    // Merge activityLogs + notes into a single timeline, sorted by time
                    $stageLabels = [
                        'interested'           => 'Interested',
                        'converted'            => 'Converted',
                        'expired'              => 'Expired',
                        'no_longer_interested' => 'No Longer Interested',
                    ];
                    $stageDots = [
                        'interested' => 'bg-yellow-400',
                        'converted'  => 'bg-green-400',
                        'expired'    => 'bg-surface-4',
                        'no_longer_interested' => 'bg-surface-4',
                    ];
                    $aColors = ['bg-fuchsia-500/30 text-fuchsia-200','bg-blue-500/30 text-blue-200','bg-green-500/30 text-green-200','bg-yellow-500/30 text-yellow-200','bg-orange-500/30 text-orange-200'];
                @endphp

                {{-- Lead created --}}
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-fuchsia-500/15 flex items-center justify-center shrink-0 mt-0.5">
                        <x-heroicon-o-bolt class="w-3.5 h-3.5 text-fuchsia-400" />
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-fg-muted"><span class="font-semibold text-fg">{{ $ld->creator?->name ?? 'System' }}</span> created a lead</p>
                            <p class="text-fg-muted/60 text-xs">{{ $ld->created_at->format('d M Y, g:ia') }}</p>
                        </div>
                        <div class="mt-2 bg-surface-2 border border-surface rounded-lg px-4 py-2.5">
                            <p class="text-fg text-xs font-medium">Created the lead.</p>
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-full bg-fuchsia-500/30 text-fuchsia-200 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">{{ $cInitials }}</div>
                </div>

                {{-- Activity logs (pipeline stage changes, conversation links, etc.) --}}
                @foreach($activityLogs as $log)
                @php
                    $logActorName  = $log->actor?->name ?? 'System';
                    $logInitials   = strtoupper(substr($logActorName, 0, 1)) . strtoupper(substr(strstr($logActorName, ' ') ?: '', 1, 1));
                    $logActorColor = $aColors[($log->actor_id ?? 0) % count($aColors)];
                    $toStage       = $log->properties['to']   ?? null;
                    $fromStage     = $log->properties['from'] ?? null;
                    $convId        = $log->properties['conversation_id'] ?? null;
                @endphp
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-fuchsia-500/15 flex items-center justify-center shrink-0 mt-0.5">
                        @if($log->event === 'conversation_linked')
                            <x-heroicon-o-chat-bubble-left-right class="w-3.5 h-3.5 text-fuchsia-400" />
                        @else
                            <x-heroicon-o-arrow-path class="w-3.5 h-3.5 text-fuchsia-400" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            @if($log->event === 'conversation_linked')
                                <p class="text-xs text-fg-muted"><span class="font-semibold text-fg">{{ $logActorName }}</span> linked a conversation</p>
                            @else
                                <p class="text-xs text-fg-muted"><span class="font-semibold text-fg">{{ $logActorName }}</span> {{ $fromStage ? 'updated' : 'set' }} the pipeline stage</p>
                            @endif
                            <p class="text-fg-muted/60 text-xs">{{ $log->performed_at->format('d M Y, g:ia') }}</p>
                        </div>
                        <div class="mt-2 bg-surface-2 border border-surface rounded-lg px-4 py-2.5">
                            @if($log->event === 'conversation_linked')
                                <p class="text-fg text-xs">
                                    Linked to
                                    @if($ld->conversation && $ld->conversation->id == $convId)
                                        <a href="{{ route('conversations.show', $ld->conversation) }}" class="text-fuchsia-400 hover:underline font-medium">Conversation #{{ $convId }}</a>.
                                    @else
                                        <span class="text-fuchsia-300 font-medium">Conversation #{{ $convId }}</span>.
                                    @endif
                                </p>
                            @else
                                <p class="text-fg text-xs inline-flex items-center gap-2 flex-wrap">
                                    @if($fromStage)
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full {{ $stageDots[$fromStage] ?? 'bg-surface-4' }} inline-block"></span>
                                        <span class="text-fg-muted">{{ $stageLabels[$fromStage] ?? ucfirst($fromStage) }}</span>
                                    </span>
                                    <x-heroicon-o-arrow-right class="w-3 h-3 text-fg-muted/50 shrink-0" />
                                    @endif
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full {{ $stageDots[$toStage] ?? 'bg-fuchsia-400' }} inline-block"></span>
                                        <span class="font-semibold text-fuchsia-300">{{ $stageLabels[$toStage] ?? ucfirst($toStage ?? '—') }}</span>
                                    </span>
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-full {{ $logActorColor }} flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">{{ $logInitials }}</div>
                </div>
                @endforeach

                {{-- Conversation notes --}}
                @foreach($notes as $note)
                @php
                    $nInitials = strtoupper(substr($note->author?->name ?? '?', 0, 1)) . strtoupper(substr(strstr($note->author?->name ?? '', ' ') ?: '', 1, 1));
                    $nColor = $aColors[($note->user_id ?? 0) % count($aColors)];
                @endphp
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-full bg-fuchsia-500/15 flex items-center justify-center shrink-0 mt-0.5">
                        @if($note->type === 'event')
                            <x-heroicon-o-phone class="w-3.5 h-3.5 text-fuchsia-400" />
                        @else
                            <x-heroicon-o-bolt class="w-3.5 h-3.5 text-fuchsia-400" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-fg-muted"><span class="font-semibold text-fg">{{ $note->author?->name ?? 'Agent' }}</span> {{ $note->type === 'event' ? 'made an outbound call' : 'added a note' }}</p>
                            <p class="text-fg-muted/60 text-xs">{{ $note->created_at->format('d M Y, g:ia') }}</p>
                        </div>
                        <div class="mt-2 bg-surface-2 border border-surface rounded-lg px-4 py-2.5">
                            <p class="text-fg text-xs whitespace-pre-line">{{ $note->content }}</p>
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-full {{ $nColor }} flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">{{ $nInitials }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- RIGHT: Opportunities --}}
        <div class="w-56 shrink-0 overflow-y-auto">
            <div class="px-4 py-3 border-b border-surface">
                <p class="font-bold text-fg text-sm">Opportunities</p>
                <p class="text-fg-muted text-xs mt-0.5">Specific opportunities identified on this lead.</p>
            </div>

            @if($ldUnits->isNotEmpty())
            <div class="px-4 py-3 border-b border-surface">
                <p class="font-semibold text-fg text-xs mb-1">Units</p>
                <p class="text-fg-muted text-xs mb-3">A summary of the selected units.</p>
                @foreach($ldUnits as $u)
                <div class="bg-surface-2 border border-surface rounded-lg px-3 py-2.5 mb-2">
                    <div class="flex items-center justify-between mb-1">
                        <p class="text-fg text-xs font-bold">{{ $u['size'] ?? '?' }}</p>
                        <p class="text-fg text-sm font-bold">${{ number_format($u['push_rate'] ?? 0, 0) }}</p>
                    </div>
                    <div class="flex items-center gap-1.5 text-fg-muted text-xs">
                        <x-heroicon-o-square-2-stack class="w-3 h-3 shrink-0" />
                        <span>{{ $u['size'] ?? '?' }} @ Street Rate</span>
                    </div>
                    @if($ld->property_protection)
                    <div class="flex items-center gap-1.5 text-fg-muted text-xs mt-0.5">
                        <x-heroicon-o-shield-check class="w-3 h-3 shrink-0" />
                        <span>${{ number_format($ld->property_protection * 1000, 0) }} coverage @ $12.00 month <span class="text-green-400">+ $12</span></span>
                    </div>
                    @endif
                    <div class="flex items-center gap-1.5 text-fg-muted text-xs mt-0.5">
                        <x-heroicon-o-tag class="w-3 h-3 shrink-0" />
                        <span>{{ ($ld->promo && $ld->promo !== '-') ? $ld->promo : 'No Discount' }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif

            <div class="px-4 py-3 border-b border-surface">
                <p class="font-semibold text-fg text-xs mb-1">Move-In Costs</p>
                <p class="text-fg-muted text-xs mb-3">The charges payable at move-in.</p>
                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <div><p class="text-fg font-medium">Move in Rent</p><p class="text-fg-muted">Move in Rent</p></div>
                        <p class="text-fg font-semibold">${{ number_format($ldMoveInRent, 0) }}</p>
                    </div>
                    <div class="flex items-center justify-between">
                        <div><p class="text-fg font-medium">Administrative Fee</p><p class="text-fg-muted">Administrative Fee</p></div>
                        <p class="text-fg font-semibold">${{ $ldAdminFee }}</p>
                    </div>
                    @if($ldInsurance > 0)
                    <div class="flex items-center justify-between">
                        <div><p class="text-fg font-medium">Insurance</p><p class="text-fg-muted">Insurance</p></div>
                        <p class="text-fg font-semibold">${{ number_format($ldInsurance, 0) }}</p>
                    </div>
                    @endif
                    <div class="flex items-center justify-between border-t border-surface pt-2 mt-1">
                        <div><p class="text-fg font-bold text-sm">Total Move In Cost</p><p class="text-fg-muted">Tax Included</p></div>
                        <div class="text-right"><p class="text-fg font-bold text-base">${{ number_format($ldTotal, 0) }}</p><p class="text-fg-muted">$0</p></div>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3">
                <p class="font-semibold text-fg text-xs mb-1">Calls</p>
                <p class="text-fg-muted text-xs mb-3">All calls associated with this conversation.</p>
                @if($ld->conversation)
                <div class="bg-surface-2 border border-surface rounded-lg px-3 py-2.5 mb-2">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-phone class="w-4 h-4 text-green-400 shrink-0" />
                        <div class="flex-1 min-w-0">
                            <p class="text-fg text-xs font-medium">{{ ucfirst($ld->conversation->direction ?? 'Inbound') }}</p>
                            <p class="text-fg-muted text-xs">{{ $ld->conversation->created_at->format('d M Y, g:ia') }}</p>
                        </div>
                        @if($ld->conversation->duration_label)
                        <span class="text-fg-muted text-xs shrink-0">{{ $ld->conversation->duration_label }}</span>
                        @endif
                    </div>
                    <div class="mt-2 flex items-center justify-center">
                        <button class="w-7 h-7 flex items-center justify-center rounded-full border border-surface hover:bg-surface-3 transition">
                            <x-heroicon-o-play class="w-3.5 h-3.5 text-fg-muted" />
                        </button>
                    </div>
                    @if($ld->conversation->completedByAgent)
                    @php $cbInit = strtoupper(substr($ld->conversation->completedByAgent->name, 0, 1)) . strtoupper(substr(strstr($ld->conversation->completedByAgent->name, ' ') ?: '', 1, 1)); @endphp
                    <div class="flex items-center gap-2 mt-2 pt-2 border-t border-surface">
                        <div class="w-6 h-6 rounded-full bg-blue-500/30 text-blue-200 flex items-center justify-center text-xs font-bold shrink-0">{{ $cbInit }}</div>
                        <div>
                            <p class="text-fg text-xs font-medium leading-tight">{{ $ld->conversation->completedByAgent->name }}</p>
                        </div>
                    </div>
                    @endif
                </div>
                @else
                <p class="text-fg-muted text-xs">No calls yet.</p>
                @endif
            </div>
        </div>

    </div>

</div>