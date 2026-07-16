@php
    $ld          = $lead;
    $dynData     = $ld->dynamic_data ?? [];
    $name        = trim(($dynData['first_name'] ?? $ld->first_name ?? '') . ' ' . ($dynData['last_name'] ?? $ld->last_name ?? ''));
    $phone       = $dynData['phone'] ?? $dynData['phone_number'] ?? $ld->phone ?? null;
    $email       = $dynData['email'] ?? $ld->email ?? null;
    $initials    = strtoupper(substr($name ?: '?', 0, 1)) . strtoupper(substr(strstr($name, ' ') ?: '', 1, 1));
    $statusClass = match (strtolower($ld->status ?? '')) {
        'open', 'new' => 'bg-yellow-400/15 text-yellow-300 border-yellow-400/25',
        'won'         => 'bg-green-400/15 text-green-300 border-green-400/25',
        'lost'        => 'bg-red-400/15 text-red-300 border-red-400/25',
        default       => 'bg-surface-2 text-fg-muted border-surface',
    };
    $stageConfig = [
        'interested'           => ['label' => 'Interested',     'dot' => 'bg-yellow-400', 'text' => 'text-yellow-400'],
        'converted'            => ['label' => 'Converted',      'dot' => 'bg-green-400',  'text' => 'text-green-400'],
        'expired'              => ['label' => 'Expired',        'dot' => 'bg-zinc-500',   'text' => 'text-fg-muted'],
        'no_longer_interested' => ['label' => 'Not Interested', 'dot' => 'bg-zinc-500',   'text' => 'text-fg-muted'],
    ];
    $currentStage = $stageConfig[$ld->pipeline_stage ?? 'interested'] ?? $stageConfig['interested'];

    // Group template fields by step
    $stepGroups = [];
    foreach ($templateFields as $key => $def) {
        $step = $def['step'] ?? 'Details';
        $stepGroups[$step][$key] = $def;
    }
@endphp

<div class="flex flex-col h-full bg-base">

    {{-- Top bar --}}
    <div class="flex items-center justify-between h-14 px-5 bg-surface border-b border-surface shrink-0">
        <div class="flex items-center gap-3">
            <a href="{{ route('leads.index') }}"
                class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-hover transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/>
                </svg>
            </a>
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-indigo-500/25 text-indigo-300 flex items-center justify-center text-xs font-bold shrink-0">
                    {{ $initials ?: '?' }}
                </div>
                <div>
                    <p class="font-semibold text-fg text-sm leading-tight">{{ $name ?: 'Unnamed Lead' }}</p>
                    <p class="text-fg-muted text-xs">Lead #{{ $ld->id }}@if ($ld->campaign) · {{ $ld->campaign->name }}@endif</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center px-2.5 py-1 rounded-lg border font-semibold text-xs {{ $statusClass }}">
                {{ ucfirst(strtolower($ld->status ?? 'New')) }}
            </span>
            @if ($ld->conversation)
                <a href="{{ route('conversations.show', $ld->conversation) }}"
                    class="inline-flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z"/>
                    </svg>
                    View Conversation
                </a>
            @endif
        </div>
    </div>

    {{-- Body --}}
    <div class="flex flex-1 min-h-0 overflow-hidden">

        {{-- LEFT: Lead data --}}
        <div class="flex-1 overflow-y-auto">
            <div class="max-w-2xl mx-auto px-6 py-6 space-y-5">

                {{-- Contact details --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="px-5 py-3.5 border-b border-surface">
                        <p class="font-semibold text-fg text-sm">Contact Details</p>
                    </div>
                    <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-4">
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Name</p>
                            <p class="text-fg text-sm">{{ $name ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Phone</p>
                            @if ($phone)
                                <a href="tel:{{ $phone }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-mono">{{ $phone }}</a>
                            @else
                                <p class="text-fg-muted text-sm">—</p>
                            @endif
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Email</p>
                            @if ($email)
                                <a href="mailto:{{ $email }}" class="text-indigo-400 hover:text-indigo-300 text-sm">{{ $email }}</a>
                            @else
                                <p class="text-fg-muted text-sm">—</p>
                            @endif
                        </div>
                        @if ($ld->campaign)
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Campaign</p>
                            <p class="text-fg text-sm">{{ $ld->campaign->name }}</p>
                        </div>
                        @endif
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Created By</p>
                            <p class="text-fg text-sm">{{ $ld->creator?->name ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">Date</p>
                            <p class="text-fg text-sm">{{ $ld->created_at->format('j M Y, g:ia') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Template fields grouped by step --}}
                @if (!empty($stepGroups))
                    @foreach ($stepGroups as $stepTitle => $fields)
                        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                            <div class="px-5 py-3.5 border-b border-surface">
                                <p class="font-semibold text-fg text-sm">{{ $stepTitle }}</p>
                            </div>
                            <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-4">
                                @foreach ($fields as $key => $def)
                                    @php $val = $dynData[$key] ?? null; @endphp
                                    <div>
                                        <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">{{ $def['label'] }}</p>
                                        @if ($val !== null && $val !== '')
                                            @if ($def['type'] === 'checkbox')
                                                <span class="text-sm {{ $val ? 'text-green-400' : 'text-fg-muted' }}">{{ $val ? 'Yes' : 'No' }}</span>
                                            @elseif ($def['type'] === 'email')
                                                <a href="mailto:{{ $val }}" class="text-indigo-400 hover:text-indigo-300 text-sm">{{ $val }}</a>
                                            @elseif ($def['type'] === 'phone')
                                                <a href="tel:{{ $val }}" class="text-indigo-400 hover:text-indigo-300 text-sm font-mono">{{ $val }}</a>
                                            @elseif ($def['type'] === 'textarea')
                                                <p class="text-fg text-sm whitespace-pre-wrap col-span-2">{{ $val }}</p>
                                            @else
                                                <p class="text-fg text-sm">{{ $val }}</p>
                                            @endif
                                        @else
                                            <p class="text-fg-muted/40 text-sm">—</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @elseif (!empty(array_filter($dynData)))
                    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                        <div class="px-5 py-3.5 border-b border-surface">
                            <p class="font-semibold text-fg text-sm">Captured Data</p>
                        </div>
                        <div class="px-5 py-4 grid grid-cols-2 gap-x-8 gap-y-4">
                            @foreach (array_filter($dynData, fn($v) => $v !== '' && $v !== null) as $k => $v)
                                <div>
                                    <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-1">{{ ucfirst(str_replace('_', ' ', $k)) }}</p>
                                    <p class="text-fg text-sm">{{ is_array($v) ? implode(', ', $v) : $v }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-surface border border-surface rounded-xl px-5 py-8 text-center">
                        <p class="text-fg-muted text-sm">No form data captured for this lead.</p>
                        @if (!$ld->campaign?->leadTemplate)
                            <p class="text-fg-muted/60 text-xs mt-1">Assign a lead template to this campaign to capture structured data.</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>

        {{-- RIGHT: Pipeline + Activity --}}
        <div class="w-64 shrink-0 border-l border-surface flex flex-col overflow-hidden">

            {{-- Pipeline stage --}}
            <div class="px-4 py-4 border-b border-surface shrink-0" x-data="{ open: false }">
                <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide mb-2.5">Pipeline Stage</p>
                <button @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-2.5 bg-surface-2 border border-surface hover:border-zinc-600 rounded-lg transition">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full {{ $currentStage['dot'] }} shrink-0"></span>
                        <span class="text-sm font-medium {{ $currentStage['text'] }}">{{ $currentStage['label'] }}</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-fg-muted" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak @click.outside="open = false" x-transition
                    class="mt-1 bg-surface-2 border border-surface rounded-xl shadow-xl overflow-hidden">
                    @foreach ($stageConfig as $key => $s)
                        <button wire:click="setPipelineStage('{{ $key }}')" @click="open = false"
                            class="w-full flex items-center gap-2.5 px-3 py-2.5 hover:bg-hover transition text-left {{ $ld->pipeline_stage === $key ? 'bg-hover' : '' }}">
                            <span class="w-2 h-2 rounded-full {{ $s['dot'] }} shrink-0"></span>
                            <span class="text-sm {{ $ld->pipeline_stage === $key ? 'font-semibold text-fg' : 'text-fg-muted' }}">{{ $s['label'] }}</span>
                            @if ($ld->pipeline_stage === $key)
                                <svg class="w-3.5 h-3.5 text-indigo-400 ml-auto shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                </svg>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Quick info --}}
            <div class="px-4 py-3 border-b border-surface shrink-0 space-y-2.5 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-fg-muted">Status</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded border font-semibold text-[11px] {{ $statusClass }}">
                        {{ ucfirst(strtolower($ld->status ?? 'New')) }}
                    </span>
                </div>
                @if ($ld->conversation)
                <div class="flex items-center justify-between">
                    <span class="text-fg-muted">Conversation</span>
                    <a href="{{ route('conversations.show', $ld->conversation) }}"
                        class="text-indigo-400 hover:text-indigo-300">#{{ $ld->conversation->id }}</a>
                </div>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-fg-muted">Age</span>
                    <span class="text-fg">{{ $ld->created_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Activity --}}
            <div class="flex-1 overflow-y-auto">
                <div class="px-4 py-3 border-b border-surface">
                    <p class="text-[11px] font-semibold text-fg-muted uppercase tracking-wide">Activity</p>
                </div>
                <div class="px-4 py-4 space-y-4">

                    <div class="flex items-start gap-2.5">
                        <div class="w-5 h-5 rounded-full bg-indigo-500/15 flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-fg-muted">
                                <span class="font-medium text-fg">{{ $ld->creator?->name ?? 'System' }}</span> created this lead
                            </p>
                            <p class="text-[10px] text-fg-muted/50 mt-0.5">{{ $ld->created_at->format('j M Y, g:ia') }}</p>
                        </div>
                    </div>

                    @foreach ($activityLogs as $log)
                        @php
                            $logActor  = $log->actor?->name ?? 'System';
                            $toStage   = $log->properties['to'] ?? null;
                        @endphp
                        <div class="flex items-start gap-2.5">
                            <div class="w-5 h-5 rounded-full bg-indigo-500/15 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-fg-muted">
                                    <span class="font-medium text-fg">{{ $logActor }}</span>
                                    @if ($toStage)
                                        → <span class="{{ $stageConfig[$toStage]['text'] ?? 'text-fg' }} font-medium">{{ $stageConfig[$toStage]['label'] ?? ucfirst($toStage) }}</span>
                                    @else
                                        {{ $log->action }}
                                    @endif
                                </p>
                                <p class="text-[10px] text-fg-muted/50 mt-0.5">{{ $log->performed_at->format('j M Y, g:ia') }}</p>
                            </div>
                        </div>
                    @endforeach

                </div>
            </div>

        </div>
    </div>
</div>
