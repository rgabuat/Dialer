@php
    $statusMap = [
        'in_progress' => ['label' => 'In Progress', 'class' => 'bg-fuchsia-500/15 text-fuchsia-300'],
        'completed' => ['label' => 'Complete', 'class' => 'bg-green-500/15 text-accent-green'],
        'queued' => ['label' => 'Queued', 'class' => 'bg-yellow-500/15 text-accent-yellow'],
        'abandoned' => ['label' => 'Abandoned', 'class' => 'bg-red-500/15 text-accent-red'],
    ];
    $s = $statusMap[$conversation->status] ?? [
        'label' => ucfirst($conversation->status),
        'class' => 'bg-surface-2 text-fg-muted',
    ];

    $avatarColors = [
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
    $contactColor = $avatarColors[$conversation->id % count($avatarColors)];

    $channelTitle = ucfirst($conversation->direction) . ' ' . ucfirst($conversation->channel);
@endphp

<div x-data="{
    _main: null,
    init() {
        this._main = this.$el.closest('main');
        if (this._main) {
            this._main._prevOverflow = this._main.style.overflowY;
            this._main.style.overflowY = 'hidden';
            this._main.style.display = 'flex';
            this._main.style.flexDirection = 'column';
        }
        const wrap = this.$el.parentElement;
        if (wrap) { wrap.style.flex = '1';
            wrap.style.minHeight = '0';
            wrap.style.display = 'flex';
            wrap.style.flexDirection = 'column'; }
    },
    destroy() {
        if (this._main) {
            this._main.style.overflowY = this._main._prevOverflow || '';
            this._main.style.display = '';
            this._main.style.flexDirection = '';
        }
    }
}"
    class="relative flex flex-1 bg-surface border border-surface rounded-xl min-h-0 [overflow:clip]" wire:poll.10s>

    {{-- ===================== LEFT PANEL ===================== --}}
    <div class="flex flex-col flex-1 border-surface border-r min-w-0 overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-4 border-surface border-b shrink-0">
            <div class="flex flex-wrap justify-between items-start gap-3">
                {{-- Title + breadcrumbs --}}
                <div class="min-w-0">
                    <h1 class="font-bold text-fg text-lg leading-tight">{{ $channelTitle }}</h1>
                    <nav class="flex items-center gap-1.5 mt-0.5 text-fg-muted text-xs">
                        @if ($conversation->campaign)
                            <a href="{{ route('campaigns.index') }}" wire:navigate
                                class="max-w-[120px] hover:text-fg truncate transition">{{ $conversation->campaign->name }}</a>
                            <span>/</span>
                        @endif
                        @if ($conversation->queue)
                            <span class="truncate">{{ $conversation->queue }}</span>
                        @endif
                    </nav>
                </div>

                {{-- ID + status + action --}}
                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <span class="font-mono text-fg-muted text-sm"># {{ $conversation->id }}</span>
                    <span
                        class="inline-flex items-center px-2.5 py-1 rounded-full font-semibold text-xs {{ $s['class'] }}">
                        {{ $s['label'] }}
                    </span>
                    @if ($conversation->status !== 'completed')
                        <button wire:click="closeConversation" wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition">
                            <x-heroicon-o-check class="w-3.5 h-3.5" />
                            <span wire:loading.remove wire:target="closeConversation">Close Conversation</span>
                            <span wire:loading wire:target="closeConversation">Closing…</span>
                        </button>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 bg-surface-2 px-3 py-1.5 rounded-lg font-semibold text-fg-muted text-xs cursor-default">
                            <x-heroicon-o-check-circle class="w-3.5 h-3.5 text-accent-green" />
                            Closed
                        </span>
                    @endif
                </div>
            </div>

        </div>

        {{-- Tabs --}}
        <div class="flex items-center bg-surface-3/20 px-5 border-surface border-b shrink-0">
            {{-- Main tabs --}}
            <div class="flex items-center gap-0">
                @foreach (['timeline' => 'Timeline', 'account' => 'Account'] as $tabKey => $tabLabel)
                    <button type="button" wire:click="$set('activeTab', '{{ $tabKey }}')"
                        class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition
                        {{ $activeTab === $tabKey ? 'border-fuchsia-500 text-fg' : 'border-transparent text-fg-muted hover:text-fg' }}">
                        {{ $tabLabel }}
                    </button>
                @endforeach
            </div>
            {{-- Account sub-nav (same row, pushed to right) --}}
            @if ($activeTab === 'account' && $accountView !== 'lead' && $accountView !== 'lead_detail')
                <nav class="flex items-center gap-0 -mb-px ml-auto">
                    @foreach (['overview' => 'Overview', 'leads' => 'Leads', 'rentals' => 'Rentals', 'notes' => 'Notes', 'contacts' => 'Contacts'] as $v => $vLabel)
                        <button wire:click="$set('accountView', '{{ $v }}')"
                            class="px-4 py-2.5 text-xs font-semibold border-b-2 -mb-px transition {{ $accountView === $v ? 'border-fuchsia-400 text-fg' : 'border-transparent text-fg-muted hover:text-fg' }}">
                            {{ $vLabel }}
                        </button>
                    @endforeach
                </nav>
            @endif
        </div>

        {{-- Scrollable content area --}}
        <div class="flex-1 overflow-y-auto">
            @if ($activeTab === 'timeline')
                <div class="space-y-7 p-6">

                    {{-- System: initial status event --}}
                    {{-- Channel event header block --}}
                    <div class="flex items-start gap-3 pb-4 border-surface border-b">
                        <div
                            class="shrink-0 w-9 h-9 rounded-full {{ $contactColor }} flex items-center justify-center font-bold text-white text-sm mt-0.5">
                            {{ $conversation->initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            {{-- Name + channel --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="font-semibold text-fg text-sm">{{ $conversation->contact_name ?? 'Unknown' }}</span>
                                @if ($conversation->contact_phone)
                                    <span
                                        class="font-mono text-fg-muted text-xs">{{ $conversation->contact_phone }}</span>
                                @endif
                                <span class="inline-flex items-center gap-1 text-fg-muted/60 text-xs">
                                    @if ($conversation->channel === 'voice')
                                        <x-heroicon-o-phone class="w-3 h-3 shrink-0" />
                                    @elseif ($conversation->channel === 'sms')
                                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-3 h-3 shrink-0" />
                                    @elseif ($conversation->channel === 'email')
                                        <x-heroicon-o-envelope class="w-3 h-3 shrink-0" />
                                    @else
                                        <x-heroicon-o-chat-bubble-oval-left class="w-3 h-3 shrink-0" />
                                    @endif
                                    {{ ucfirst($conversation->direction) }} {{ ucfirst($conversation->channel) }}
                                </span>
                            </div>

                            {{-- Stats + date inline --}}
                            <div class="flex flex-wrap items-center gap-4 mt-1.5">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-calendar class="w-3 h-3 text-fg-muted/60 shrink-0" />
                                    <span
                                        class="text-fg-muted text-xs">{{ ($conversation->started_at ?? $conversation->created_at)->format('j M Y, g:ia') }}</span>
                                </div>
                                @if ($conversation->duration_seconds !== null)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-fg-muted text-xs">Duration</span>
                                        <span
                                            class="font-mono font-semibold text-fg text-xs">{{ $conversation->duration_label }}</span>
                                    </div>
                                @endif
                                <div class="flex items-center gap-1.5">
                                    <span class="text-fg-muted text-xs">Answered</span>
                                    <span
                                        class="font-semibold text-fg text-xs">{{ in_array($conversation->status, ['in_progress', 'completed']) ? 'Yes' : 'No' }}</span>
                                </div>
                                @if ($conversation->to_number)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-fg-muted text-xs">DID</span>
                                        <span class="font-mono text-fg text-xs">{{ $conversation->to_number }}</span>
                                    </div>
                                @endif
                                @php
                                    $callerLocation = array_filter([
                                        $conversation->caller_city,
                                        $conversation->caller_state,
                                        $conversation->caller_zip,
                                    ]);
                                @endphp
                                @if ($callerLocation)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-fg-muted text-xs">Location</span>
                                        <span class="text-fg text-xs">{{ implode(', ', $callerLocation) }}</span>
                                    </div>
                                @endif
                                @if ($conversation->caller_name)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-fg-muted text-xs">Caller</span>
                                        <span class="text-fg text-xs">{{ $conversation->caller_name }}</span>
                                    </div>
                                @endif
                                @if ($conversation->forwarded_from)
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-fg-muted text-xs">Fwd From</span>
                                        <span
                                            class="font-mono text-fg text-xs">{{ $conversation->forwarded_from }}</span>
                                    </div>
                                @endif
                                @if ($conversation->detail_preview)
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="text-fg-muted text-xs shrink-0">Preview</span>
                                        <span
                                            class="text-fg-muted/70 text-xs truncate italic">{{ $conversation->detail_preview }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @forelse ($notes as $note)
                        @php
                            $author = $note->author;
                            $authorInitials = $author
                                ? strtoupper(
                                    substr($author->first_name ?? '', 0, 1) . substr($author->last_name ?? '', 0, 1),
                                )
                                : 'SY';
                            $authorColor = $author ? $avatarColors[$author->id % count($avatarColors)] : 'bg-surface-3';
                        @endphp
                        <div class="flex items-start gap-3" wire:key="note-{{ $note->id }}">
                            <div class="mt-0.5 shrink-0">
                                @if ($note->type === 'event')
                                    <div
                                        class="flex justify-center items-center bg-surface-2 border border-surface rounded-full w-7 h-7">
                                        <x-heroicon-o-information-circle class="w-4 h-4 text-fg-muted" />
                                    </div>
                                @else
                                    <div
                                        class="w-7 h-7 rounded-full {{ $authorColor }} flex items-center justify-center font-bold text-white text-xs">
                                        {{ $authorInitials }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-center gap-2 mb-1.5">
                                    <span class="font-semibold text-fg text-sm">
                                        {{ $author ? $author->first_name . ' ' . $author->last_name : 'System' }}
                                    </span>
                                    <span
                                        class="text-fg-muted/50 text-xs">{{ $note->created_at->diffForHumans() }}</span>
                                </div>
                                @if ($note->type === 'event')
                                    <p class="text-fg-muted text-sm italic">{{ $note->content }}</p>
                                @else
                                    <div
                                        class="bg-surface-2 px-4 py-3 border border-surface rounded-xl text-fg text-sm leading-relaxed whitespace-pre-wrap">
                                        {{ trim($note->content) }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col justify-center items-center gap-2 py-8 text-center">
                            <div
                                class="flex justify-center items-center bg-surface-2 border border-surface rounded-full w-10 h-10">
                                <x-heroicon-o-chat-bubble-left-ellipsis class="w-5 h-5 text-fg-muted/40" />
                            </div>
                            <p class="text-fg-muted text-sm">No notes yet</p>
                            <p class="text-fg-muted/60 text-xs">Add a note below to keep track of this conversation.</p>
                        </div>
                    @endforelse

                </div>
            @else
                {{-- Account tab --}}
                <div class="flex flex-col">

                    {{-- OVERVIEW --}}
                    @if ($accountView === 'overview')
                        <div>

                            {{-- Basics section --}}
                            <div class="grid grid-cols-[200px_1fr] border-surface border-b">
                                <div class="px-6 py-6">
                                    <p class="font-semibold text-fg text-sm">Basics</p>
                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Core details for this account.
                                    </p>
                                </div>
                                <div class="py-4 pr-6 pl-4 divide-y divide-surface">
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Account Name</span>
                                        <span
                                            class="font-medium text-fg text-xs">{{ $conversation->contact_name ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Remote Type</span>
                                        <span class="text-fg-muted text-xs">—</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Remote ID</span>
                                        <span class="text-fg-muted text-xs">—</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Type</span>
                                        <span class="text-fg-muted text-xs">—</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Brand</span>
                                        <span
                                            class="font-medium text-fg text-xs">{{ $accountStore?->brand ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Store</span>
                                        <span
                                            class="font-medium text-fg text-xs">{{ $accountStore?->name ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions section --}}
                            <div class="grid grid-cols-[200px_1fr]">
                                <div class="px-6 py-6">
                                    <p class="font-semibold text-fg text-sm">Actions</p>
                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Common actions for this
                                        account.</p>
                                </div>
                                <div class="py-5 pr-6 pl-4">
                                    <div
                                        class="flex justify-between items-center gap-4 bg-surface-2 p-4 border border-surface rounded-xl">
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Create Lead</p>
                                            <p class="mt-0.5 text-fg-muted text-xs leading-relaxed">Search for
                                                available storage and create quotes, reservations, or rentals.</p>
                                        </div>
                                        <button wire:click="$set('accountView', 'lead')"
                                            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition shrink-0">
                                            Create Lead
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                    {{-- LEADS VIEW --}}
                    @if ($accountView === 'leads')
                        <div>
                            {{-- Header --}}
                            <div class="flex justify-between items-center px-6 py-4 border-surface border-b">
                                <div>
                                    <p class="font-bold text-fg text-sm leading-tight">Account Leads</p>
                                    <p class="text-fg-muted text-xs">Existing NSA leads on this account.</p>
                                </div>
                                <button wire:click="$set('accountView', 'lead')"
                                    class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition">
                                    Create Lead
                                </button>
                            </div>

                            {{-- Leads table --}}
                            <div>
                                @if ($leads->isEmpty())
                                    <div class="flex flex-col justify-center items-center gap-3 py-16">
                                        <x-heroicon-o-document-text class="w-9 h-9 text-fg-muted/25" />
                                        <p class="font-medium text-fg-muted text-sm">No leads yet</p>
                                        <p class="text-fg-muted/50 text-xs">Create a quote, reservation, or rental to
                                            get started.</p>
                                    </div>
                                @else
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                                                <th class="px-5 py-3 text-left">Type</th>
                                                <th class="px-5 py-3 text-left">Status</th>
                                                <th class="px-5 py-3 text-left">Value</th>
                                                <th class="px-5 py-3 text-left">Started</th>
                                                <th class="px-5 py-3 text-left">Expires</th>
                                                <th class="px-5 py-3 text-left">Details</th>
                                                <th class="px-5 py-3 text-left">Created</th>
                                                <th class="px-5 py-3 text-left">Created By</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-surface">
                                            @foreach ($leads as $lead)
                                                @php
                                                    $typeLabel = match ($lead->lead_type) {
                                                        'quote' => 'Self Storage Quote',
                                                        'reservation' => 'Self Storage Reservation',
                                                        'waitlist' => 'Self Storage Waitlist',
                                                        'rental' => 'Self Storage Rental',
                                                        default => ucfirst($lead->lead_type ?? '—'),
                                                    };
                                                    $unitSummary = collect($lead->selected_units ?? [])
                                                        ->map(
                                                            fn($u) => ($u['size'] ?? '?') .
                                                                ' @ $' .
                                                                ($u['push_rate'] ?? '?'),
                                                        )
                                                        ->join(', ');
                                                    $totalValue = collect($lead->selected_units ?? [])->sum(
                                                        fn($u) => ($u['push_rate'] ?? 0) * ($u['qty'] ?? 1),
                                                    );
                                                @endphp
                                                <tr wire:click="selectLead({{ $lead->id }})"
                                                    class="hover:bg-surface-2/50 transition cursor-pointer">
                                                    <td class="px-5 py-4 font-medium text-fg">{{ $typeLabel }}</td>
                                                    <td class="px-5 py-4">
                                                        <span
                                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($lead->status, ['NEW', 'OPEN']) ? 'bg-yellow-400/20 text-yellow-300' : 'bg-surface-3 text-fg-muted' }}">
                                                            Open
                                                        </span>
                                                    </td>
                                                    <td class="px-5 py-4 text-fg">${{ number_format($totalValue, 0) }}
                                                    </td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">
                                                        {{ $lead->created_at->diffForHumans() }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">
                                                        {{ $lead->move_in_date?->format('d M Y') ?? '—' }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">
                                                        {{ $unitSummary ?: '—' }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">
                                                        {{ $lead->created_at->format('d M Y') }}</td>
                                                    <td class="px-5 py-4">
                                                        @if ($lead->creator)
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="inline-flex justify-center items-center bg-yellow-400 rounded-full w-6 h-6 font-bold text-black text-xs shrink-0">
                                                                    {{ strtoupper(substr($lead->creator->name, 0, 1)) }}{{ strtoupper(substr(strstr($lead->creator->name, ' ') ?: '', 1, 1)) }}
                                                                </span>
                                                                <span
                                                                    class="text-fg-muted text-xs">{{ $lead->creator->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-fg-muted text-xs">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- RENTALS VIEW --}}
                    @if ($accountView === 'rentals')
                        <div>
                            <div class="flex justify-between items-center px-6 py-4 border-surface border-b">
                                <div>
                                    <p class="font-bold text-fg text-sm leading-tight">Account Rentals</p>
                                    <p class="text-fg-muted text-xs">Existing rentals setup on this Self Storage
                                        account.</p>
                                </div>
                            </div>
                            <div class="flex flex-col justify-center items-center gap-3 py-16">
                                <x-heroicon-o-building-storefront class="w-9 h-9 text-fg-muted/25" />
                                <p class="font-medium text-fg-muted text-sm">No rentals found.</p>
                            </div>
                        </div>
                    @endif

                    {{-- NOTES VIEW --}}
                    @if ($accountView === 'notes')
                        <div>
                            <div class="flex justify-between items-center px-6 py-4 border-surface border-b">
                                <div>
                                    <p class="font-bold text-fg text-sm leading-tight">Account Notes</p>
                                    <p class="text-fg-muted text-xs">Notes stored on the selected account.</p>
                                </div>
                            </div>
                            <div>
                                @if ($notes->isEmpty())
                                    <div class="flex flex-col justify-center items-center gap-3 py-16">
                                        <x-heroicon-o-chat-bubble-left-ellipsis class="w-9 h-9 text-fg-muted/25" />
                                        <p class="font-medium text-fg-muted text-sm">No notes yet</p>
                                    </div>
                                @else
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                                                <th class="px-6 py-3 text-left">Note</th>
                                                <th class="px-6 py-3 text-left">Created By</th>
                                                <th class="px-6 py-3 text-left">Created</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-surface">
                                            @foreach ($notes as $note)
                                                <tr class="hover:bg-surface-2/50 align-top transition">
                                                    <td class="px-6 py-4 max-w-xs text-fg text-xs whitespace-pre-line">
                                                        {{ $note->content }}</td>
                                                    <td class="px-6 py-4">
                                                        @if ($note->author)
                                                            <div class="flex items-center gap-2">
                                                                <span
                                                                    class="inline-flex justify-center items-center bg-fuchsia-500/30 rounded-full w-6 h-6 font-bold text-fuchsia-300 text-xs shrink-0">
                                                                    {{ strtoupper(substr($note->author->name, 0, 1)) }}{{ strtoupper(substr(strstr($note->author->name, ' ') ?: '', 1, 1)) }}
                                                                </span>
                                                                <span
                                                                    class="text-fg-muted text-xs">{{ $note->author->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-fg-muted text-xs">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-fg-muted text-xs">
                                                        {{ $note->created_at->format('d M Y, H:i') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- CONTACTS VIEW --}}
                    @if ($accountView === 'contacts')
                        <div>
                            <div class="flex justify-between items-center px-6 py-4 border-surface border-b">
                                <div>
                                    <p class="font-bold text-fg text-sm leading-tight">Account Contacts</p>
                                    <p class="text-fg-muted text-xs">Contacts associated with the selected account.</p>
                                </div>
                            </div>
                            <div>
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-surface border-b font-semibold text-fg-muted text-xs">
                                            <th class="px-6 py-3 text-left">Name</th>
                                            <th class="px-6 py-3 text-left">Primary</th>
                                            <th class="px-6 py-3 text-left">Created</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-surface">
                                        <tr class="hover:bg-surface-2/50 transition">
                                            <td class="px-6 py-4">
                                                <div>
                                                    <p class="font-medium text-fg text-xs">
                                                        {{ $conversation->contact_name ?? '—' }}</p>
                                                    @if ($conversation->contact_phone)
                                                        <p class="mt-0.5 text-fg-muted text-xs">
                                                            {{ $conversation->contact_phone }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-green-400" />
                                            </td>
                                            <td class="px-6 py-4 text-fg-muted text-xs">
                                                {{ $conversation->created_at->format('d M Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- LEAD DETAIL VIEW --}}
                    @if ($accountView === 'lead_detail' && $selectedLead)
                        @php
                            $ld = $selectedLead;
                            $ldTypeLabel = match ($ld->lead_type) {
                                'quote' => 'Quote',
                                'reservation' => 'Reservation',
                                'waitlist' => 'Waitlist',
                                'rental' => 'Rental',
                                default => ucfirst($ld->lead_type ?? '—'),
                            };
                            $ldUnits = collect($ld->selected_units ?? []);
                            $ldMoveInRent = $ldUnits->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
                            $ldInsurance = $ld->property_protection ? (float) $ld->property_protection : 0;
                            $ldTotal = $ldMoveInRent + $ldInsurance;
                        @endphp
                        <div class="flex min-h-0">

                            {{-- LEFT: Overview --}}
                            @php
                                $ldName = trim(($ld->first_name ?? '') . ' ' . ($ld->last_name ?? ''));
                                $ldInitials =
                                    strtoupper(substr($ld->first_name ?? '?', 0, 1)) .
                                    strtoupper(substr($ld->last_name ?? '', 0, 1));
                            @endphp
                            <div class="border-surface border-r w-52 overflow-y-auto shrink-0">

                                {{-- Back + title --}}
                                <div class="flex items-start gap-2 px-4 py-3 border-surface border-b">
                                    <button wire:click="$set('accountView', 'leads')"
                                        class="flex justify-center items-center hover:bg-surface-2 mt-0.5 rounded w-6 h-6 text-fg-muted hover:text-fg transition shrink-0">
                                        <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                                    </button>
                                    <div>
                                        <p class="font-bold text-fg text-sm leading-tight">Sales Lead</p>
                                        <p class="mt-0.5 text-fg-muted text-xs leading-snug">A Self Storage
                                            {{ $ldTypeLabel }} Sales lead.</p>
                                    </div>
                                </div>

                                {{-- Contact card --}}
                                <div
                                    class="flex flex-col items-center gap-2 px-4 py-4 border-surface border-b text-center">
                                    <div
                                        class="flex justify-center items-center bg-fuchsia-500/30 rounded-full w-10 h-10 font-bold text-fuchsia-200 text-sm shrink-0">
                                        {{ $ldInitials ?: '?' }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-fg text-sm leading-tight">{{ $ldName ?: '—' }}</p>
                                        @if ($ld->store)
                                            <p class="mt-0.5 text-fg-muted text-xs">{{ $ld->store->name }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Overview section header --}}
                                <div class="bg-surface-2/20 px-4 py-2.5 border-surface border-b">
                                    <p class="font-bold text-fg text-xs">Overview</p>
                                    <p class="mt-0.5 text-fg-muted text-xs">Overview of this lead.</p>
                                </div>

                                {{-- Core fields --}}
                                <div class="divide-y divide-surface">
                                    {{-- Category --}}
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Category</span>
                                        <span class="font-medium text-fg text-xs text-right">Sales</span>
                                    </div>
                                    {{-- Type --}}
                                    <div class="items-center gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Type</span>
                                        <span class="inline-flex justify-end">
                                            <span
                                                class="bg-fuchsia-500/20 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">{{ $ldTypeLabel }}</span>
                                        </span>
                                    </div>
                                    {{-- Status --}}
                                    <div class="items-center gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Status</span>
                                        <span class="inline-flex justify-end">
                                            <span
                                                class="bg-yellow-400/20 px-2 py-0.5 rounded-full font-semibold text-yellow-300 text-xs">{{ ucfirst(strtolower($ld->status ?? 'New')) }}</span>
                                        </span>
                                    </div>
                                    {{-- Pipeline --}}
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Pipeline</span>
                                        <span class="font-medium text-fg text-xs text-right">Sales</span>
                                    </div>
                                    {{-- Pipeline Stage --}}
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Pipeline Stage</span>
                                        <span class="font-medium text-fuchsia-400 text-xs text-right">Interested</span>
                                    </div>
                                    {{-- Created --}}
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Created</span>
                                        <span
                                            class="font-medium text-fg text-xs text-right leading-tight">{{ $ld->created_at->format('d M Y, g:ia') }}</span>
                                    </div>
                                    {{-- Store --}}
                                    @if ($ld->store)
                                        <div class="px-4 py-2.5">
                                            <span class="block mb-1 text-fg-muted text-xs">Store</span>
                                            <span
                                                class="font-medium text-fuchsia-400 text-xs leading-snug">{{ $ld->store->name }}<br>
                                                <span
                                                    class="text-fg-muted">{{ implode(', ', array_filter([$ld->store->city, $ld->store->state])) }}</span>
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Contact section --}}
                                <div class="bg-surface-2/20 mt-1 px-4 py-2.5 border-surface border-t border-b">
                                    <p class="font-semibold text-fg-muted text-xs uppercase tracking-wider">Contact</p>
                                </div>
                                <div class="divide-y divide-surface">
                                    @if ($ldName)
                                        <div class="flex items-center gap-3 px-4 py-2.5">
                                            <div
                                                class="flex justify-center items-center bg-fuchsia-500/30 rounded-full w-6 h-6 font-bold text-fuchsia-200 text-xs shrink-0">
                                                {{ $ldInitials }}</div>
                                            <div>
                                                <p class="font-semibold text-fg text-xs">{{ $ldName }}</p>
                                                @if ($ld->store)
                                                    <p class="text-fg-muted text-xs">{{ $ld->store->name }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Account</span>
                                        <span
                                            class="font-medium text-fuchsia-400 text-xs text-right">{{ $ldName ?: '—' }}</span>
                                    </div>
                                    <div class="px-4 py-2.5">
                                        <span class="block text-fg-muted text-xs">Phone</span>
                                        <span class="font-medium text-fg text-xs">{{ $ld->phone ?? '—' }}</span>
                                        @if ($ld->phone)
                                            <p class="text-fg-muted text-xs">Mobile</p>
                                        @endif
                                    </div>
                                    @if ($ld->email)
                                        <div class="px-4 py-2.5">
                                            <span class="block text-fg-muted text-xs">Email</span>
                                            <span
                                                class="font-medium text-fg text-xs break-all">{{ $ld->email }}</span>
                                        </div>
                                    @endif
                                    @if ($ld->address)
                                        <div class="px-4 py-2.5">
                                            <span class="block text-fg-muted text-xs">Address</span>
                                            <span class="font-medium text-fg text-xs">{{ $ld->address }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Storage Info section --}}
                                <div class="bg-surface-2/20 mt-1 px-4 py-2.5 border-surface border-t border-b">
                                    <p class="font-semibold text-fg-muted text-xs uppercase tracking-wider">Storage
                                        Info</p>
                                </div>
                                <div class="divide-y divide-surface">
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Move In Date</span>
                                        <span
                                            class="font-medium text-fuchsia-400 text-xs text-right">{{ $ld->move_in_date?->format('j M Y') ?? '—' }}</span>
                                    </div>
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Duration</span>
                                        <span
                                            class="font-medium text-fg text-xs text-right">{{ $ld->duration ?? '—' }}</span>
                                    </div>
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Reason For</span>
                                        <span
                                            class="font-medium text-fg text-xs text-right">{{ $ld->reason_for_storage ?? '—' }}</span>
                                    </div>
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Types Of Items</span>
                                        <span
                                            class="font-medium text-fg text-xs text-right">{{ $ld->types_of_items ?? '—' }}</span>
                                    </div>
                                    <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                        <span class="text-fg-muted text-xs">Created By</span>
                                        <span
                                            class="font-medium text-fg text-xs text-right">{{ $ld->creator?->name ?? '—' }}</span>
                                    </div>
                                </div>

                            </div>

                            {{-- MIDDLE: Activity --}}
                            <div class="flex-1 border-surface border-r overflow-y-auto">
                                <div class="px-5 py-3 border-surface border-b">
                                    <p class="font-bold text-fg text-sm">Activity</p>
                                    <p class="text-fg-muted text-xs">A history of activity on this lead.</p>
                                </div>
                                <div class="space-y-5 p-5">
                                    {{-- Lead created event --}}
                                    <div class="flex items-start gap-3">
                                        <div
                                            class="flex justify-center items-center bg-fuchsia-500/15 mt-0.5 rounded-full w-7 h-7 shrink-0">
                                            <x-heroicon-o-bolt class="w-3.5 h-3.5 text-fuchsia-400" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-fg-muted text-xs"><span
                                                    class="font-semibold text-fg">{{ $ld->creator?->name ?? 'System' }}</span>
                                                created a lead</p>
                                            <div class="bg-surface-2 mt-2 px-3 py-2 border border-surface rounded-lg">
                                                <p class="font-medium text-fg text-xs">Created the lead.</p>
                                            </div>
                                            <p class="mt-1 text-fg-muted/60 text-xs">
                                                {{ $ld->created_at->format('d M Y g:ia') }}</p>
                                        </div>
                                    </div>
                                    {{-- Conversation notes --}}
                                    @foreach ($notes as $note)
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="flex justify-center items-center bg-surface-2 mt-0.5 border border-surface rounded-full w-7 h-7 font-bold text-fg text-xs shrink-0">
                                                {{ strtoupper(substr($note->author?->name ?? '?', 0, 1)) }}{{ strtoupper(substr(strstr($note->author?->name ?? '', ' ') ?: '', 1, 1)) }}
                                            </div>
                                            <div class="flex-1">
                                                <p class="text-fg-muted text-xs"><span
                                                        class="font-semibold text-fg">{{ $note->author?->name ?? 'Agent' }}</span>
                                                    added a note</p>
                                                <div
                                                    class="bg-surface-2 mt-2 px-3 py-2 border border-surface rounded-lg">
                                                    <p class="text-fg text-xs whitespace-pre-line">
                                                        {{ $note->content }}</p>
                                                </div>
                                                <p class="mt-1 text-fg-muted/60 text-xs">
                                                    {{ $note->created_at->format('d M Y g:ia') }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- RIGHT: Opportunities --}}
                            <div class="w-64 overflow-y-auto shrink-0">
                                <div class="px-4 py-3 border-surface border-b">
                                    <p class="font-bold text-fg text-sm">Opportunities</p>
                                    <p class="text-fg-muted text-xs">Specifics opportunities identified on this lead.
                                    </p>
                                </div>
                                {{-- Units --}}
                                @if ($ldUnits->isNotEmpty())
                                    <div class="px-4 py-3 border-surface border-b">
                                        <p class="mb-2 font-semibold text-fg text-xs">Units</p>
                                        <p class="mb-3 text-fg-muted text-xs">A summary of the selected units.</p>
                                        @foreach ($ldUnits as $u)
                                            @php
                                                $uStore = collect($stores)->firstWhere('id', $u['store_id'] ?? null);
                                                $uUnit = collect($uStore['units'] ?? [])->firstWhere(
                                                    'id',
                                                    $u['unit_id'] ?? null,
                                                );
                                            @endphp
                                            <div
                                                class="bg-surface-2 mb-2 px-3 py-2.5 border border-surface rounded-lg">
                                                <div class="flex justify-between items-center">
                                                    <p class="font-semibold text-fg text-xs">
                                                        {{ $uUnit['size'] ?? ($u['size'] ?? '?') }}</p>
                                                    <p class="font-bold text-fg text-xs">
                                                        ${{ number_format($u['push_rate'] ?? 0, 0) }}</p>
                                                </div>
                                                <p class="mt-0.5 text-fg-muted text-xs">Street Rate:
                                                    ${{ number_format($u['street_rate'] ?? 0, 0) }}/mo</p>
                                                @if ($ld->property_protection)
                                                    <p class="text-fg-muted text-xs">Protection:
                                                        +${{ $ld->property_protection }}/mo</p>
                                                @endif
                                                @if ($ld->promo && $ld->promo !== '-')
                                                    <p class="text-fg-muted text-xs">Promo: {{ $ld->promo }}</p>
                                                @endif
                                                @if ($ld->admin_fee_credit)
                                                    <p class="text-green-400 text-xs">Admin fee credited</p>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                {{-- Move-In Costs --}}
                                <div class="px-4 py-3 border-surface border-b">
                                    <p class="mb-1 font-semibold text-fg text-xs">Move-In Costs</p>
                                    <p class="mb-3 text-fg-muted text-xs">The charges payable at move-in.</p>
                                    <div class="space-y-1.5">
                                        <div class="flex justify-between items-center">
                                            <span class="text-fg-muted text-xs">Move in Rent</span>
                                            <span
                                                class="font-semibold text-fg text-xs">${{ number_format($ldMoveInRent, 0) }}</span>
                                        </div>
                                        @if ($ldInsurance > 0)
                                            <div class="flex justify-between items-center">
                                                <span class="text-fg-muted text-xs">Insurance</span>
                                                <span
                                                    class="font-semibold text-fg text-xs">${{ number_format($ldInsurance, 0) }}</span>
                                            </div>
                                        @endif
                                        <div
                                            class="flex justify-between items-center mt-2 pt-2 border-surface border-t">
                                            <span class="font-bold text-fg text-xs">Total Move In Cost</span>
                                            <span
                                                class="font-bold text-fg text-sm">${{ number_format($ldTotal, 0) }}</span>
                                        </div>
                                    </div>
                                </div>
                                {{-- Calls --}}
                                <div class="px-4 py-3">
                                    <p class="mb-1 font-semibold text-fg text-xs">Calls</p>
                                    <p class="mb-3 text-fg-muted text-xs">All calls associated with this conversation.
                                    </p>
                                    <div class="bg-surface-2 px-3 py-2.5 border border-surface rounded-lg">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-o-phone class="w-4 h-4 text-green-400 shrink-0" />
                                            <div>
                                                <p class="font-medium text-fg text-xs">
                                                    {{ ucfirst($conversation->direction ?? 'Inbound') }}</p>
                                                <p class="text-fg-muted text-xs">
                                                    {{ $conversation->created_at->format('d M Y, g:ia') }}</p>
                                            </div>
                                            @if ($conversation->duration_label)
                                                <span
                                                    class="ml-auto text-fg-muted text-xs">{{ $conversation->duration_label }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endif

                    {{-- CREATE LEAD (dynamic by campaign lead inputs) --}}
                    @if ($accountView === 'lead')
                        <div x-data="{}" @lead-created.window="$wire.set('accountView', 'leads')"
                            class="flex flex-col flex-1 min-h-0">

                            {{-- Validation / process errors (outside wire:ignore — updated by Livewire) --}}
                            @if (!empty($leadCreateErrors))
                                <div class="px-5 py-3 bg-red-500/10 border-b border-red-500/30 shrink-0 space-y-1">
                                    @foreach ($leadCreateErrors as $err)
                                        <p class="text-accent-red text-xs flex items-start gap-1.5">
                                            <x-heroicon-o-exclamation-circle class="w-3.5 h-3.5 shrink-0 mt-0.5" />
                                            {{ $err }}
                                        </p>
                                    @endforeach
                                </div>
                            @endif

                            <div x-data="{
                                process: {{ Js::from($leadCreateProcess) }},
                                stores: {{ Js::from($leadCreateStores->map(fn($s) => ['id' => $s->id, 'name' => $s->name])->values()) }},
                                step: 0,
                                formData: {},
                                saving: false,
                                init() {
                                    (this.process.steps || []).forEach((st) => {
                                        (st.fields || []).forEach((f) => {
                                            if (Object.prototype.hasOwnProperty.call(this.formData, f.key)) return;
                                            this.formData[f.key] = f.type === 'checkbox' ? false : '';
                                        });
                                    });
                                },
                                currentStep() {
                                    return (this.process.steps || [])[this.step] || { title: 'Lead', fields: [] };
                                },
                                inputType(field) {
                                    return field.type === 'phone' ? 'tel' : field.type;
                                },
                                canMoveNext() {
                                    const st = this.currentStep();
                                    return (st.fields || []).every((f) => {
                                        if (!f.required) return true;
                                        const v = this.formData[f.key];
                                        if (f.type === 'checkbox') return !!v;
                                        return String(v ?? '').trim() !== '';
                                    });
                                },
                                submitLead($wire) {
                                    this.saving = true;
                                    $wire.createLead(this.formData)
                                        .then(() => {
                                            this.saving = false;
                                        })
                                        .catch(() => {
                                            this.saving = false;
                                        });
                                }
                            }" wire:ignore class="flex flex-col flex-1 min-h-0">
                                <div
                                    class="flex justify-between items-center px-5 py-4 border-surface border-b shrink-0">
                                    <div class="flex items-center gap-3">
                                        <button type="button" @click="$wire.set('accountView', 'overview')"
                                            class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                                            <x-heroicon-o-arrow-left class="w-4 h-4" />
                                        </button>
                                        <div>
                                            <p class="font-bold text-fg text-sm leading-tight">Create Lead</p>
                                            <p class="text-fg-muted text-xs">Form fields are configured in Campaign
                                                Lead Process.</p>
                                        </div>
                                    </div>
                                    <div class="text-fg-muted text-xs" x-show="(process.steps || []).length > 0">
                                        <span x-text="'Step ' + (step + 1) + ' of ' + process.steps.length"></span>
                                    </div>
                                </div>

                                <div class="p-5" x-show="(process.steps || []).length === 0">
                                    <div class="bg-surface-2 border border-surface rounded-xl p-4 space-y-2">
                                        @if (!$conversation->campaign)
                                            <p class="text-fg text-sm font-medium">No campaign assigned.</p>
                                            <p class="text-fg-muted text-xs">This conversation is not linked to a
                                                campaign. The lead form is driven by the campaign's assigned lead
                                                template.</p>
                                        @else
                                            <p class="text-fg text-sm font-medium">No lead template assigned.</p>
                                            <p class="text-fg-muted text-xs">Campaign
                                                <strong>{{ $conversation->campaign->name }}</strong> has no lead
                                                template. Assign one in <strong>Admin &rarr; Lead Templates</strong>
                                                then link it to the campaign.</p>
                                            <a href="{{ route('admin.lead-templates.index') }}" wire:navigate
                                                class="inline-flex items-center gap-1.5 mt-1 text-indigo-400 hover:text-indigo-300 text-xs transition">
                                                <x-heroicon-o-rectangle-stack class="w-3.5 h-3.5" />
                                                Manage Lead Templates
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-5 space-y-4" x-show="(process.steps || []).length > 0">
                                    <div class="text-fg text-sm font-semibold"
                                        x-text="currentStep().title || 'Lead Inputs'"></div>

                                    <template x-for="field in currentStep().fields" :key="field.key">
                                        <div>
                                            <label class="block text-fg-muted text-xs mb-1.5">
                                                <span x-text="field.label"></span>
                                                <span x-show="field.required" class="text-accent-red">*</span>
                                            </label>

                                            <template x-if="field.type === 'textarea'">
                                                <textarea x-model="formData[field.key]" rows="3" :placeholder="field.placeholder || ''"
                                                    class="w-full bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none text-fg text-sm"></textarea>
                                            </template>

                                            <template x-if="field.type === 'select'">
                                                <select x-model="formData[field.key]"
                                                    class="w-full bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none text-fg text-sm">
                                                    <option value="">Select</option>
                                                    <template x-if="field.key === 'store_id'">
                                                        <template x-for="s in stores" :key="s.id">
                                                            <option :value="String(s.id)" x-text="s.name"></option>
                                                        </template>
                                                    </template>
                                                    <template x-if="field.key !== 'store_id'">
                                                        <template x-for="opt in (field.options || [])"
                                                            :key="opt">
                                                            <option :value="opt" x-text="opt"></option>
                                                        </template>
                                                    </template>
                                                </select>
                                            </template>

                                            <template x-if="field.type === 'checkbox'">
                                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" x-model="formData[field.key]"
                                                        class="w-4 h-4 accent-fuchsia-500" />
                                                    <span class="text-fg text-sm"
                                                        x-text="field.placeholder || field.label"></span>
                                                </label>
                                            </template>

                                            <template x-if="!['textarea', 'select', 'checkbox'].includes(field.type)">
                                                <input :type="inputType(field)" x-model="formData[field.key]"
                                                    :placeholder="field.placeholder || ''"
                                                    class="w-full bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none text-fg text-sm" />
                                            </template>

                                            <p x-show="field.help_text" class="text-fg-muted text-xs mt-1"
                                                x-text="field.help_text"></p>
                                        </div>
                                    </template>
                                </div>

                                <div class="border-surface border-t shrink-0"
                                    x-show="(process.steps || []).length > 0">
                                    <div class="flex justify-between items-center px-5 py-3">
                                        <button type="button" x-show="step > 0"
                                            @click="step = Math.max(step - 1, 0)"
                                            class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface-3 px-4 py-2 rounded-lg font-semibold text-fg-muted hover:text-fg text-sm transition">
                                            Back
                                        </button>
                                        <span x-show="step === 0"></span>

                                        <button type="button" x-show="step < process.steps.length - 1"
                                            @click="if (canMoveNext()) step++" :disabled="!canMoveNext()"
                                            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 disabled:opacity-50 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                                            Continue
                                        </button>

                                        <button type="button" x-show="step >= process.steps.length - 1"
                                            @click="if (!saving) submitLead($wire)"
                                            :disabled="saving || !canMoveNext()"
                                            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 disabled:opacity-50 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                                            <span x-show="!saving">Create Lead</span>
                                            <span x-show="saving">Creating...</span>
                                        </button>
                                    </div>
                                </div>
                            </div>{{-- end wire:ignore Alpine form --}}
                        </div>{{-- end outer @lead-created wrapper --}}
                    @endif

                </div>
            @endif
        </div>

        @if ($activeTab === 'timeline')
            {{-- Note composer --}}
            <div class="bg-surface border-surface border-t shrink-0" x-data="{
                note: '',
                sending: false,
                saved: false,
                fmt(pre, suf) {
                    const el = $refs.noteInput;
                    const s = el.selectionStart,
                        e = el.selectionEnd;
                    const sel = el.value.slice(s, e);
                    const rep = pre + (sel || '') + suf;
                    el.value = el.value.slice(0, s) + rep + el.value.slice(e);
                    this.note = el.value;
                    el.focus();
                    el.setSelectionRange(s + pre.length, s + pre.length + (sel ? sel.length : 0));
                },
                ins(txt) {
                    const el = $refs.noteInput;
                    const s = el.selectionStart;
                    el.value = el.value.slice(0, s) + txt + el.value.slice(s);
                    this.note = el.value;
                    el.focus();
                    const c = s + txt.length;
                    el.setSelectionRange(c, c);
                },
                submit() {
                    const content = this.note.trim();
                    if (!content || this.sending) return;
                    this.sending = true;
                    $wire.addNote(content);
                }
            }"
                @note-added.window="note = ''; sending = false; saved = true; setTimeout(() => saved = false, 2500)">

                {{-- Composer header row --}}
                <div class="flex justify-between items-center px-4 pt-3 pb-1.5">
                    <div class="flex items-center gap-1.5">
                        <span
                            class="inline-flex items-center gap-1.5 bg-fuchsia-500/10 px-2.5 py-1 border border-fuchsia-500/20 rounded-md font-semibold text-fuchsia-400 text-xs">
                            <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                            Note
                        </span>
                    </div>
                    {{-- Saved indicator --}}
                    <span x-show="saved" x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="inline-flex items-center gap-1.5 font-medium text-xs text-accent-green">
                        <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                        Note saved
                    </span>
                </div>

                {{-- Textarea --}}
                <div class="px-4 py-2">
                    <textarea x-ref="noteInput" x-model="note" placeholder="Type your note…" rows="3" :disabled="sending"
                        class="bg-transparent disabled:opacity-60 outline-none w-full text-fg placeholder:text-fg-muted/40 text-sm leading-relaxed resize-none"></textarea>
                </div>

                {{-- Toolbar --}}
                <div class="flex justify-between items-center px-3 pt-2 pb-3 border-surface/40 border-t">
                    <div class="flex items-center gap-0.5">
                        <button type="button" @click="fmt('**', '**')" title="Bold"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 font-bold text-fg-muted hover:text-fg text-xs transition">B</button>
                        <button type="button" @click="fmt('*', '*')" title="Italic"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 font-serif text-fg-muted hover:text-fg text-xs italic transition">I</button>
                        <button type="button" @click="fmt('__', '__')" title="Underline"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg text-xs underline transition">U</button>
                        <button type="button" @click="fmt('\`', '\`')" title="Code"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-code-bracket class="w-3.5 h-3.5" />
                        </button>
                        <div class="bg-surface-2 mx-1.5 w-px h-4"></div>
                        <button type="button" @click="ins('\n1. ')" title="Ordered list"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-list-bullet class="w-3.5 h-3.5" />
                        </button>
                        <button type="button" @click="ins('\n- ')" title="Bullet list"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-bars-3 class="w-3.5 h-3.5" />
                        </button>
                        <button type="button" @click="fmt('[', '](url)')" title="Link"
                            class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-link class="w-3.5 h-3.5" />
                        </button>
                    </div>
                    <button type="button" @click="submit()" :disabled="!note.trim() || sending"
                        :class="note.trim() && !sending ?
                            'bg-fuchsia-600 hover:bg-fuchsia-500 text-white cursor-pointer' :
                            'bg-surface-2 text-fg-muted cursor-not-allowed opacity-60'"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-semibold text-xs transition">
                        <template x-if="!sending">
                            <span class="flex items-center gap-1.5">
                                <x-heroicon-o-paper-airplane class="w-3.5 h-3.5" />
                                Add Note
                            </span>
                        </template>
                        <template x-if="sending">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Saving…
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        @endif
    </div>

    {{-- ===================== RIGHT SIDEBAR ===================== --}}
    <div class="flex flex-col w-72 xl:w-80 overflow-y-auto shrink-0">

        {{-- Contact header --}}
        <div class="px-4 pt-5 pb-4 border-surface border-b shrink-0">
            <div class="flex justify-between items-start gap-2">
                <div class="flex items-center gap-3 min-w-0">
                    <div
                        class="shrink-0 w-10 h-10 rounded-full {{ $contactColor }} flex items-center justify-center font-bold text-white">
                        {{ $conversation->initials }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-fg truncate">{{ $conversation->contact_name ?? 'Unknown' }}</p>
                        @if ($conversation->campaign)
                            <p class="text-fg-muted text-xs truncate">{{ $conversation->campaign->name }}</p>
                        @endif
                    </div>
                </div>
                <button type="button"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition shrink-0">
                    <x-heroicon-o-ellipsis-vertical class="w-4 h-4" />
                </button>
            </div>
            @if ($conversation->contact_phone)
                <div class="flex items-center gap-2 mt-3 text-sm">
                    <span class="w-16 text-fg-muted text-xs shrink-0">Phone</span>
                    <span class="font-mono text-fg text-xs">{{ $conversation->contact_phone }}</span>
                </div>
            @endif
            @php
                $sidebarLocation = array_filter([
                    $conversation->caller_city,
                    $conversation->caller_state,
                    $conversation->caller_country,
                ]);
            @endphp
            @if ($sidebarLocation)
                <div class="flex items-start gap-2 mt-1.5">
                    <span class="w-16 text-fg-muted text-xs shrink-0">Location</span>
                    <span class="text-fg-muted text-xs">{{ implode(', ', $sidebarLocation) }}</span>
                </div>
            @endif
            @if ($conversation->caller_name && $conversation->caller_name !== ($conversation->contact_name ?? ''))
                <div class="flex items-center gap-2 mt-1.5">
                    <span class="w-16 text-fg-muted text-xs shrink-0">CNAM</span>
                    <span class="text-fg text-xs">{{ $conversation->caller_name }}</span>
                </div>
            @endif
        </div>

        {{-- Capabilities / tags --}}
        @if ($conversation->campaign || $conversation->contact_phone)
            <div class="space-y-2.5 px-4 py-3 border-surface border-b shrink-0">
                @if ($conversation->campaign)
                    <div class="flex items-start gap-2.5">
                        <div
                            class="flex justify-center items-center bg-fuchsia-500/15 mt-0.5 rounded-full w-5 h-5 shrink-0">
                            <x-heroicon-s-megaphone class="w-3 h-3 text-fuchsia-400" />
                        </div>
                        <div>
                            <p class="font-semibold text-fg text-xs">Campaigns Enabled</p>
                            <p class="text-fg-muted text-xs">Can recieve campaign messages.</p>
                        </div>
                    </div>
                @endif
                @if ($conversation->contact_phone)
                    <div class="flex items-start gap-2.5">
                        <div
                            class="flex justify-center items-center bg-green-500/15 mt-0.5 rounded-full w-5 h-5 shrink-0">
                            <x-heroicon-s-heart class="w-3 h-3 text-accent-green" />
                        </div>
                        <div>
                            <p class="font-semibold text-fg text-xs">Surveys Enabled</p>
                            <p class="text-fg-muted text-xs">Can receive surveys.</p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Sidebar tabs --}}
        <div class="flex items-center px-1 border-surface border-b shrink-0">
            @foreach (['details' => 'Details', 'account' => 'Account', 'store' => 'Store', 'script' => 'Script'] as $key => $label)
                @if ($key === 'script' && !$conversation->campaign?->script)
                    @continue
                @endif
                <button type="button" wire:click="$set('sidebarTab', '{{ $key }}')"
                    class="px-3 py-2.5 text-xs font-semibold border-b-2 -mb-px transition
                        {{ $sidebarTab === $key ? 'border-fuchsia-500 text-fg' : 'border-transparent text-fg-muted hover:text-fg' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Sidebar tab content --}}
        <div class="flex-1 p-4 overflow-y-auto">
            @if ($sidebarTab === 'details')
                <h3 class="mb-0.5 font-semibold text-fg text-sm">Details</h3>
                <p class="mb-4 text-fg-muted text-xs">Details about this conversation.</p>
                <dl class="space-y-3">
                    @if ($conversation->assignedAgent)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Assigned To</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->assignedAgent->first_name }}
                                {{ $conversation->assignedAgent->last_name }}</dd>
                        </div>
                    @endif
                    @if ($conversation->completedByAgent)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Completed By</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->completedByAgent->first_name }}
                                {{ $conversation->completedByAgent->last_name }}</dd>
                        </div>
                    @endif
                    @if ($conversation->to_number)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">DID Called</dt>
                            <dd class="font-mono font-medium text-fg text-xs text-right">
                                {{ $conversation->to_number }}</dd>
                        </div>
                    @endif
                    @if ($conversation->forwarded_from)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Forwarded From</dt>
                            <dd class="font-mono font-medium text-fg text-xs text-right">
                                {{ $conversation->forwarded_from }}</dd>
                        </div>
                    @endif
                    @if ($conversation->caller_country)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Country</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->caller_country }}</dd>
                        </div>
                    @endif
                    @if ($conversation->call_sid)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Call SID</dt>
                            <dd class="font-mono text-[10px] text-fg-muted text-right break-all">
                                {{ $conversation->call_sid }}</dd>
                        </div>
                    @endif
                    <div class="space-y-3 pt-3 border-surface border-t">
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Created At</dt>
                            <dd class="text-fg text-xs text-right">
                                {{ $conversation->created_at->format('j M Y g:ia') }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Updated At</dt>
                            <dd class="text-fg text-xs text-right">
                                {{ $conversation->updated_at->format('j M Y g:ia') }}</dd>
                        </div>
                    </div>
                </dl>
            @elseif ($sidebarTab === 'account')
                @php $firstLead = $leads->first(); @endphp
                <div>
                    <h3 class="mb-0.5 font-semibold text-fg text-sm">Account</h3>
                    <p class="mb-4 text-fg-muted text-xs">Contact and lead information.</p>
                    <dl class="space-y-3">
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Name</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->contact_name ?? '—' }}</dd>
                        </div>
                        @if ($conversation->contact_phone)
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Phone</dt>
                                <dd class="font-mono font-medium text-fg text-xs text-right">
                                    {{ $conversation->contact_phone }}</dd>
                            </div>
                        @endif
                        @if ($conversation->campaign)
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Campaign</dt>
                                <dd class="font-medium text-fg text-xs text-right">
                                    {{ $conversation->campaign->name }}</dd>
                            </div>
                        @endif
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Leads</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                @if ($leads->count() > 0)
                                    <span
                                        class="inline-flex items-center bg-fuchsia-500/15 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">{{ $leads->count() }}
                                        lead{{ $leads->count() === 1 ? '' : 's' }}</span>
                                @else
                                    <span class="text-fg-muted">None</span>
                                @endif
                            </dd>
                        </div>
                        @if ($firstLead)
                            <div class="space-y-3 pt-3 border-surface border-t">
                                <dt class="font-semibold text-fg-muted text-xs uppercase tracking-wide">Latest Lead
                                </dt>
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-28 text-fg-muted text-xs shrink-0">Type</dt>
                                    <dd>
                                        <span
                                            class="inline-flex items-center bg-fuchsia-500/20 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">
                                            {{ ucfirst($firstLead->lead_type ?? '—') }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-28 text-fg-muted text-xs shrink-0">Status</dt>
                                    <dd>
                                        <span
                                            class="inline-flex items-center bg-yellow-400/20 px-2 py-0.5 rounded-full font-semibold text-yellow-300 text-xs">
                                            {{ ucfirst(strtolower($firstLead->status ?? 'New')) }}
                                        </span>
                                    </dd>
                                </div>
                                @if ($firstLead->store)
                                    <div class="flex justify-between items-start gap-2">
                                        <dt class="w-28 text-fg-muted text-xs shrink-0">Store</dt>
                                        <dd class="font-medium text-fuchsia-400 text-xs text-right">
                                            {{ $firstLead->store->name }}</dd>
                                    </div>
                                @endif
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-28 text-fg-muted text-xs shrink-0">Created</dt>
                                    <dd class="text-fg text-xs text-right">
                                        {{ $firstLead->created_at->format('j M Y') }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>
                    <div class="space-y-2 mt-5">
                        @if ($leads->isNotEmpty())
                            <button wire:click="$set('activeTab', 'account'); $set('accountView', 'leads')"
                                class="inline-flex justify-center items-center gap-1.5 bg-surface-2 hover:bg-surface-3 px-3 py-2 border border-surface rounded-lg w-full font-semibold text-fg text-xs transition">
                                <x-heroicon-o-document-text class="w-3.5 h-3.5 text-fg-muted" />
                                View All Leads ({{ $leads->count() }})
                            </button>
                        @endif
                        <button wire:click="$set('activeTab', 'account'); $set('accountView', 'lead')"
                            class="inline-flex justify-center items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-2 rounded-lg w-full font-semibold text-white text-xs transition">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            Create New Lead
                        </button>
                    </div>
                </div>
            @elseif ($sidebarTab === 'script' && $conversation->campaign?->script)
                <div class="space-y-2">
                    <h3 class="font-semibold text-fg text-sm">Campaign Script</h3>
                    <p class="text-fg-muted text-xs">{{ $conversation->campaign->name }}</p>
                    <div
                        class="bg-surface-2 p-3 border border-surface rounded-xl text-fg text-xs leading-relaxed whitespace-pre-wrap">
                        {{ $conversation->campaign->script }}</div>
                </div>
            @elseif ($sidebarTab === 'store')
                @if ($accountStore)
                    <div>
                        <h3 class="mb-0.5 font-semibold text-fg text-sm">Store</h3>
                        <p class="mb-4 text-fg-muted text-xs">Linked storage facility details.</p>
                        <dl class="space-y-3">
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-20 text-fg-muted text-xs shrink-0">Name</dt>
                                <dd class="font-medium text-fg text-xs text-right">{{ $accountStore->name }}</dd>
                            </div>
                            @if ($accountStore->address)
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-20 text-fg-muted text-xs shrink-0">Address</dt>
                                    <dd class="font-medium text-fg text-xs text-right">{{ $accountStore->address }}
                                    </dd>
                                </div>
                            @endif
                            @if ($accountStore->city || $accountStore->state)
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-20 text-fg-muted text-xs shrink-0">Location</dt>
                                    <dd class="font-medium text-fg text-xs text-right">
                                        {{ implode(', ', array_filter([$accountStore->city, $accountStore->state, $accountStore->zip])) }}
                                    </dd>
                                </div>
                            @endif
                            @if (isset($accountStore->occupancy))
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-20 text-fg-muted text-xs shrink-0">Occupancy</dt>
                                    <dd class="font-medium text-fg text-xs text-right">
                                        {{ $accountStore->occupancy }}%</dd>
                                </div>
                            @endif
                            @if ($accountStore->phone ?? null)
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-20 text-fg-muted text-xs shrink-0">Phone</dt>
                                    <dd class="font-mono text-fg text-xs text-right">{{ $accountStore->phone }}</dd>
                                </div>
                            @endif
                            @if ($accountStore->type ?? null)
                                <div class="flex justify-between items-start gap-2">
                                    <dt class="w-20 text-fg-muted text-xs shrink-0">Type</dt>
                                    <dd class="font-medium text-fg text-xs text-right capitalize">
                                        {{ $accountStore->type }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                @else
                    <div class="flex flex-col justify-center items-center py-12 text-center">
                        <x-heroicon-o-building-storefront class="mb-2 w-8 h-8 text-fg-muted/30" />
                        <p class="font-medium text-fg-muted text-sm">No store linked</p>
                        <p class="mt-1 text-fg-muted/60 text-xs">Create a lead and select a store to link one.</p>
                        <button wire:click="$set('activeTab', 'account'); $set('accountView', 'lead')"
                            class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 mt-4 px-3 py-1.5 rounded-lg font-semibold text-white text-xs transition">
                            <x-heroicon-o-plus class="w-3.5 h-3.5" />
                            Create Lead
                        </button>
                    </div>
                @endif
            @else
                <div class="flex flex-col justify-center items-center py-12 text-center">
                    <x-heroicon-o-document-text class="mb-2 w-8 h-8 text-fg-muted/30" />
                    <p class="text-fg-muted text-xs">Nothing to show here.</p>
                </div>
            @endif
        </div>
    </div>


</div>
