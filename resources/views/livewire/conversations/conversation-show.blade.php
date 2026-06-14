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
        if (wrap) { wrap.style.flex = '1'; wrap.style.minHeight = '0'; wrap.style.display = 'flex'; wrap.style.flexDirection = 'column'; }
    },
    destroy() {
        if (this._main) {
            this._main.style.overflowY = this._main._prevOverflow || '';
            this._main.style.display = '';
            this._main.style.flexDirection = '';
        }
    }
}" class="relative flex flex-1 bg-surface border border-surface rounded-xl min-h-0 [overflow:clip]"
    wire:poll.10s>

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
                @foreach(['overview' => 'Overview', 'leads' => 'Leads', 'rentals' => 'Rentals', 'notes' => 'Notes', 'contacts' => 'Contacts'] as $v => $vLabel)
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
                        <div class="shrink-0 w-9 h-9 rounded-full {{ $contactColor }} flex items-center justify-center font-bold text-white text-sm mt-0.5">
                            {{ $conversation->initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            {{-- Name + channel --}}
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-fg text-sm">{{ $conversation->contact_name ?? 'Unknown' }}</span>
                                @if ($conversation->contact_phone)
                                    <span class="font-mono text-fg-muted text-xs">{{ $conversation->contact_phone }}</span>
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
                                    <span class="text-fg-muted text-xs">{{ ($conversation->started_at ?? $conversation->created_at)->format('j M Y, g:ia') }}</span>
                                </div>
                                @if ($conversation->duration_seconds !== null)
                                <div class="flex items-center gap-1.5">
                                    <span class="text-fg-muted text-xs">Duration</span>
                                    <span class="font-mono font-semibold text-fg text-xs">{{ $conversation->duration_label }}</span>
                                </div>
                                @endif
                                <div class="flex items-center gap-1.5">
                                    <span class="text-fg-muted text-xs">Answered</span>
                                    <span class="font-semibold text-fg text-xs">{{ in_array($conversation->status, ['in_progress', 'completed']) ? 'Yes' : 'No' }}</span>
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
                                    <span class="font-mono text-fg text-xs">{{ $conversation->forwarded_from }}</span>
                                </div>
                                @endif
                                @if ($conversation->detail_preview)
                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="text-fg-muted text-xs shrink-0">Preview</span>
                                    <span class="text-fg-muted/70 text-xs truncate italic">{{ $conversation->detail_preview }}</span>
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
                                        class="bg-surface-2 px-4 py-3 border border-surface rounded-xl text-fg text-sm leading-relaxed whitespace-pre-wrap">{{ trim($note->content) }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col justify-center items-center gap-2 py-8 text-center">
                            <div class="flex justify-center items-center bg-surface-2 border border-surface rounded-full w-10 h-10">
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
                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Core details for this account.</p>
                                </div>
                                <div class="py-4 pr-6 pl-4 divide-y divide-surface">
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Account Name</span>
                                        <span class="font-medium text-fg text-xs">{{ $conversation->contact_name ?? '—' }}</span>
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
                                        <span class="font-medium text-fg text-xs">{{ $accountStore?->brand ?? '—' }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-2.5">
                                        <span class="text-fg-muted text-xs">Store</span>
                                        <span class="font-medium text-fg text-xs">{{ $accountStore?->name ?? '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Actions section --}}
                            <div class="grid grid-cols-[200px_1fr]">
                                <div class="px-6 py-6">
                                    <p class="font-semibold text-fg text-sm">Actions</p>
                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Common actions for this account.</p>
                                </div>
                                <div class="py-5 pr-6 pl-4">
                                    <div class="flex justify-between items-center gap-4 bg-surface-2 p-4 border border-surface rounded-xl">
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Create Lead</p>
                                            <p class="mt-0.5 text-fg-muted text-xs leading-relaxed">Search for available storage and create quotes, reservations, or rentals.</p>
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
                                        <p class="text-fg-muted/50 text-xs">Create a quote, reservation, or rental to get started.</p>
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
                                                    $typeLabel = match($lead->lead_type) {
                                                        'quote'       => 'Self Storage Quote',
                                                        'reservation' => 'Self Storage Reservation',
                                                        'waitlist'    => 'Self Storage Waitlist',
                                                        'rental'      => 'Self Storage Rental',
                                                        default       => ucfirst($lead->lead_type ?? '—'),
                                                    };
                                                    $unitSummary = collect($lead->selected_units ?? [])
                                                        ->map(fn($u) => ($u['size'] ?? '?') . ' @ $' . ($u['push_rate'] ?? '?'))
                                                        ->join(', ');
                                                    $totalValue = collect($lead->selected_units ?? [])
                                                        ->sum(fn($u) => ($u['push_rate'] ?? 0) * ($u['qty'] ?? 1));
                                                @endphp
                                                <tr wire:click="selectLead({{ $lead->id }})" class="hover:bg-surface-2/50 transition cursor-pointer">
                                                    <td class="px-5 py-4 font-medium text-fg">{{ $typeLabel }}</td>
                                                    <td class="px-5 py-4">
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ in_array($lead->status, ['NEW','OPEN']) ? 'bg-yellow-400/20 text-yellow-300' : 'bg-surface-3 text-fg-muted' }}">
                                                            Open
                                                        </span>
                                                    </td>
                                                    <td class="px-5 py-4 text-fg">${{ number_format($totalValue, 0) }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">{{ $lead->created_at->diffForHumans() }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">{{ $lead->move_in_date?->format('d M Y') ?? '—' }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">{{ $unitSummary ?: '—' }}</td>
                                                    <td class="px-5 py-4 text-fg-muted text-xs">{{ $lead->created_at->format('d M Y') }}</td>
                                                    <td class="px-5 py-4">
                                                        @if ($lead->creator)
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex justify-center items-center bg-yellow-400 rounded-full w-6 h-6 font-bold text-black text-xs shrink-0">
                                                                    {{ strtoupper(substr($lead->creator->name, 0, 1)) }}{{ strtoupper(substr(strstr($lead->creator->name, ' ') ?: '', 1, 1)) }}
                                                                </span>
                                                                <span class="text-fg-muted text-xs">{{ $lead->creator->name }}</span>
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
                                    <p class="text-fg-muted text-xs">Existing rentals setup on this Self Storage account.</p>
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
                                                    <td class="px-6 py-4 max-w-xs text-fg text-xs whitespace-pre-line">{{ $note->content }}</td>
                                                    <td class="px-6 py-4">
                                                        @if ($note->author)
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex justify-center items-center bg-fuchsia-500/30 rounded-full w-6 h-6 font-bold text-fuchsia-300 text-xs shrink-0">
                                                                    {{ strtoupper(substr($note->author->name, 0, 1)) }}{{ strtoupper(substr(strstr($note->author->name, ' ') ?: '', 1, 1)) }}
                                                                </span>
                                                                <span class="text-fg-muted text-xs">{{ $note->author->name }}</span>
                                                            </div>
                                                        @else
                                                            <span class="text-fg-muted text-xs">—</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-fg-muted text-xs">{{ $note->created_at->format('d M Y, H:i') }}</td>
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
                                                    <p class="font-medium text-fg text-xs">{{ $conversation->contact_name ?? '—' }}</p>
                                                    @if ($conversation->contact_phone)
                                                        <p class="mt-0.5 text-fg-muted text-xs">{{ $conversation->contact_phone }}</p>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <x-heroicon-s-check-circle class="w-4 h-4 text-green-400" />
                                            </td>
                                            <td class="px-6 py-4 text-fg-muted text-xs">{{ $conversation->created_at->format('d M Y') }}</td>
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
                        $ldTypeLabel = match($ld->lead_type) {
                            'quote'       => 'Quote',
                            'reservation' => 'Reservation',
                            'waitlist'    => 'Waitlist',
                            'rental'      => 'Rental',
                            default       => ucfirst($ld->lead_type ?? '—'),
                        };
                        $ldUnits = collect($ld->selected_units ?? []);
                        $ldMoveInRent = $ldUnits->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
                        $ldInsurance  = $ld->property_protection ? (float)$ld->property_protection : 0;
                        $ldTotal      = $ldMoveInRent + $ldInsurance;
                    @endphp
                    <div class="flex min-h-0">

                        {{-- LEFT: Overview --}}
                        @php
                            $ldName = trim(($ld->first_name ?? '') . ' ' . ($ld->last_name ?? ''));
                            $ldInitials = strtoupper(substr($ld->first_name ?? '?', 0, 1)) . strtoupper(substr($ld->last_name ?? '', 0, 1));
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
                                    <p class="mt-0.5 text-fg-muted text-xs leading-snug">A Self Storage {{ $ldTypeLabel }} Sales lead.</p>
                                </div>
                            </div>

                            {{-- Contact card --}}
                            <div class="flex flex-col items-center gap-2 px-4 py-4 border-surface border-b text-center">
                                <div class="flex justify-center items-center bg-fuchsia-500/30 rounded-full w-10 h-10 font-bold text-fuchsia-200 text-sm shrink-0">
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
                                        <span class="bg-fuchsia-500/20 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">{{ $ldTypeLabel }}</span>
                                    </span>
                                </div>
                                {{-- Status --}}
                                <div class="items-center gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Status</span>
                                    <span class="inline-flex justify-end">
                                        <span class="bg-yellow-400/20 px-2 py-0.5 rounded-full font-semibold text-yellow-300 text-xs">{{ ucfirst(strtolower($ld->status ?? 'New')) }}</span>
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
                                    <span class="font-medium text-fg text-xs text-right leading-tight">{{ $ld->created_at->format('d M Y, g:ia') }}</span>
                                </div>
                                {{-- Store --}}
                                @if ($ld->store)
                                <div class="px-4 py-2.5">
                                    <span class="block mb-1 text-fg-muted text-xs">Store</span>
                                    <span class="font-medium text-fuchsia-400 text-xs leading-snug">{{ $ld->store->name }}<br>
                                        <span class="text-fg-muted">{{ implode(', ', array_filter([$ld->store->city, $ld->store->state])) }}</span>
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
                                    <div class="flex justify-center items-center bg-fuchsia-500/30 rounded-full w-6 h-6 font-bold text-fuchsia-200 text-xs shrink-0">{{ $ldInitials }}</div>
                                    <div>
                                        <p class="font-semibold text-fg text-xs">{{ $ldName }}</p>
                                        @if ($ld->store)<p class="text-fg-muted text-xs">{{ $ld->store->name }}</p>@endif
                                    </div>
                                </div>
                                @endif
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Account</span>
                                    <span class="font-medium text-fuchsia-400 text-xs text-right">{{ $ldName ?: '—' }}</span>
                                </div>
                                <div class="px-4 py-2.5">
                                    <span class="block text-fg-muted text-xs">Phone</span>
                                    <span class="font-medium text-fg text-xs">{{ $ld->phone ?? '—' }}</span>
                                    @if ($ld->phone)<p class="text-fg-muted text-xs">Mobile</p>@endif
                                </div>
                                @if ($ld->email)
                                <div class="px-4 py-2.5">
                                    <span class="block text-fg-muted text-xs">Email</span>
                                    <span class="font-medium text-fg text-xs break-all">{{ $ld->email }}</span>
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
                                <p class="font-semibold text-fg-muted text-xs uppercase tracking-wider">Storage Info</p>
                            </div>
                            <div class="divide-y divide-surface">
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Move In Date</span>
                                    <span class="font-medium text-fuchsia-400 text-xs text-right">{{ $ld->move_in_date?->format('j M Y') ?? '—' }}</span>
                                </div>
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Duration</span>
                                    <span class="font-medium text-fg text-xs text-right">{{ $ld->duration ?? '—' }}</span>
                                </div>
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Reason For</span>
                                    <span class="font-medium text-fg text-xs text-right">{{ $ld->reason_for_storage ?? '—' }}</span>
                                </div>
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Types Of Items</span>
                                    <span class="font-medium text-fg text-xs text-right">{{ $ld->types_of_items ?? '—' }}</span>
                                </div>
                                <div class="gap-2 grid grid-cols-2 px-4 py-2.5">
                                    <span class="text-fg-muted text-xs">Created By</span>
                                    <span class="font-medium text-fg text-xs text-right">{{ $ld->creator?->name ?? '—' }}</span>
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
                                    <div class="flex justify-center items-center bg-fuchsia-500/15 mt-0.5 rounded-full w-7 h-7 shrink-0">
                                        <x-heroicon-o-bolt class="w-3.5 h-3.5 text-fuchsia-400" />
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-fg-muted text-xs"><span class="font-semibold text-fg">{{ $ld->creator?->name ?? 'System' }}</span> created a lead</p>
                                        <div class="bg-surface-2 mt-2 px-3 py-2 border border-surface rounded-lg">
                                            <p class="font-medium text-fg text-xs">Created the lead.</p>
                                        </div>
                                        <p class="mt-1 text-fg-muted/60 text-xs">{{ $ld->created_at->format('d M Y g:ia') }}</p>
                                    </div>
                                </div>
                                {{-- Conversation notes --}}
                                @foreach ($notes as $note)
                                <div class="flex items-start gap-3">
                                    <div class="flex justify-center items-center bg-surface-2 mt-0.5 border border-surface rounded-full w-7 h-7 font-bold text-fg text-xs shrink-0">
                                        {{ strtoupper(substr($note->author?->name ?? '?', 0, 1)) }}{{ strtoupper(substr(strstr($note->author?->name ?? '', ' ') ?: '', 1, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-fg-muted text-xs"><span class="font-semibold text-fg">{{ $note->author?->name ?? 'Agent' }}</span> added a note</p>
                                        <div class="bg-surface-2 mt-2 px-3 py-2 border border-surface rounded-lg">
                                            <p class="text-fg text-xs whitespace-pre-line">{{ $note->content }}</p>
                                        </div>
                                        <p class="mt-1 text-fg-muted/60 text-xs">{{ $note->created_at->format('d M Y g:ia') }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- RIGHT: Opportunities --}}
                        <div class="w-64 overflow-y-auto shrink-0">
                            <div class="px-4 py-3 border-surface border-b">
                                <p class="font-bold text-fg text-sm">Opportunities</p>
                                <p class="text-fg-muted text-xs">Specifics opportunities identified on this lead.</p>
                            </div>
                            {{-- Units --}}
                            @if ($ldUnits->isNotEmpty())
                            <div class="px-4 py-3 border-surface border-b">
                                <p class="mb-2 font-semibold text-fg text-xs">Units</p>
                                <p class="mb-3 text-fg-muted text-xs">A summary of the selected units.</p>
                                @foreach ($ldUnits as $u)
                                @php
                                    $uStore = collect($stores)->firstWhere('id', $u['store_id'] ?? null);
                                    $uUnit  = collect($uStore['units'] ?? [])->firstWhere('id', $u['unit_id'] ?? null);
                                @endphp
                                <div class="bg-surface-2 mb-2 px-3 py-2.5 border border-surface rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <p class="font-semibold text-fg text-xs">{{ $uUnit['size'] ?? ($u['size'] ?? '?') }}</p>
                                        <p class="font-bold text-fg text-xs">${{ number_format($u['push_rate'] ?? 0, 0) }}</p>
                                    </div>
                                    <p class="mt-0.5 text-fg-muted text-xs">Street Rate: ${{ number_format($u['street_rate'] ?? 0, 0) }}/mo</p>
                                    @if ($ld->property_protection)
                                    <p class="text-fg-muted text-xs">Protection: +${{ $ld->property_protection }}/mo</p>
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
                                        <span class="font-semibold text-fg text-xs">${{ number_format($ldMoveInRent, 0) }}</span>
                                    </div>
                                    @if ($ldInsurance > 0)
                                    <div class="flex justify-between items-center">
                                        <span class="text-fg-muted text-xs">Insurance</span>
                                        <span class="font-semibold text-fg text-xs">${{ number_format($ldInsurance, 0) }}</span>
                                    </div>
                                    @endif
                                    <div class="flex justify-between items-center mt-2 pt-2 border-surface border-t">
                                        <span class="font-bold text-fg text-xs">Total Move In Cost</span>
                                        <span class="font-bold text-fg text-sm">${{ number_format($ldTotal, 0) }}</span>
                                    </div>
                                </div>
                            </div>
                            {{-- Calls --}}
                            <div class="px-4 py-3">
                                <p class="mb-1 font-semibold text-fg text-xs">Calls</p>
                                <p class="mb-3 text-fg-muted text-xs">All calls associated with this conversation.</p>
                                <div class="bg-surface-2 px-3 py-2.5 border border-surface rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <x-heroicon-o-phone class="w-4 h-4 text-green-400 shrink-0" />
                                        <div>
                                            <p class="font-medium text-fg text-xs">{{ ucfirst($conversation->direction ?? 'Inbound') }}</p>
                                            <p class="text-fg-muted text-xs">{{ $conversation->created_at->format('d M Y, g:ia') }}</p>
                                        </div>
                                        @if ($conversation->duration_label)
                                        <span class="ml-auto text-fg-muted text-xs">{{ $conversation->duration_label }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @endif

                    {{-- CREATE LEAD STEPPER --}}
                    @if ($accountView === 'lead')
                    <div x-data="{
                        step: 1,
                        steps: ['START', 'STORAGE', 'ACTION', 'REVIEW'],
                        lead: {
                            first_name: '',
                            last_name: '',
                            reason_for_storage: '',
                            types_of_items: '',
                            duration: '',
                        },
                        action: {
                            type: 'quote',
                            move_in_date: '{{ now()->format('Y-m-d') }}',
                            channels: { sms: false, email: true },
                            email: '',
                            property_protection: '',
                            promo: '',
                            admin_fee_credit: false,
                            unit_size: '',
                            typeDescriptions: {
                                quote: { title: 'Self Storage Quote', body: 'The customer is interested in a unit but does not want to commit right now. This will not reserve a unit for them. We\'ll send them a confirmation of the quote.' },
                                reservation: { title: 'Self Storage Reservation', body: 'The customer wants to reserve a unit of this type but not lock it in or make a payment. We\'ll send them a confirmation of the reservation.' },
                                waitlist: { title: 'Self Storage Waitlist', body: 'The customer is interested in a unit size that is not currently available. We\'ll email the store with the details for them put on their waitlist.' },
                                rental: { title: 'Self Storage Rental', body: 'The customer wants to lock in the unit, make payment and complete the process right now. We\'ll send them a link to the rental agreement and everything they need to move in.' },
                            }
                        },
                        storage: {
                            selectedStore: null,
                            selectedUnits: {},
                            filters: {
                                class: 'storage',
                                sizes: [],
                                dimensions: [],
                                attributes: [],
                                max_price: '',
                                include_unavailable: false,
                            },
                            unitQty(storeId, unitId) {
                                return (this.selectedUnits[storeId + '_' + unitId] || 0);
                            },
                            setUnitQty(storeId, unitId, qty) {
                                const key = storeId + '_' + unitId;
                                if (qty <= 0) { delete this.selectedUnits[key]; } else { this.selectedUnits[key] = qty; }
                                this.selectedUnits = {...this.selectedUnits};
                            },
                            filteredUnits(units) {
                                return units.filter(u => {
                                    const f = this.filters;
                                    if (f.sizes.length) {
                                        const sizeMap = { small: 'Small Storage', medium: 'Medium Storage', large: 'Large Storage' };
                                        const allowed = f.sizes.map(s => sizeMap[s]).filter(Boolean);
                                        if (!allowed.includes(u.category)) return false;
                                    }
                                    if (f.dimensions.length) {
                                        if (!f.dimensions.includes(u.size)) return false;
                                    }
                                    if (f.attributes.length) {
                                        if (!f.attributes.some(a => u.features.includes(a))) return false;
                                    }
                                    if (f.max_price !== '' && f.max_price !== null) {
                                        if (u.push_rate > Number(f.max_price)) return false;
                                    }
                                    if (!f.include_unavailable && u.available === 0) return false;
                                    return true;
                                });
                            },
                            stores: {{ Js::from($stores) }}
                        },
                        submitLead($wire) {
                            const units = Object.entries(this.storage.selectedUnits)
                                .filter(([, qty]) => qty > 0)
                                .map(([key, qty]) => {
                                    const [sId, uId] = key.split('_').map(Number);
                                    const store = this.storage.stores.find(s => s.id === sId);
                                    const unit = store?.units.find(u => u.id === uId);
                                    return { store_id: sId, unit_id: uId, qty, size: unit?.size, push_rate: unit?.push_rate, street_rate: unit?.street_rate };
                                });
                            $wire.createLead({
                                first_name: this.lead.first_name,
                                last_name: this.lead.last_name,
                                email: this.action.email,
                                reason_for_storage: this.lead.reason_for_storage,
                                types_of_items: this.lead.types_of_items,
                                duration: this.lead.duration,
                                lead_type: this.action.type,
                                move_in_date: this.action.move_in_date,
                                property_protection: this.action.property_protection,
                                promo: this.action.promo,
                                admin_fee_credit: this.action.admin_fee_credit,
                                unit_size: this.action.unit_size,
                                notify_sms: this.action.channels.sms,
                                notify_email: this.action.channels.email,
                                notify_email_address: this.action.email,
                                selected_units: units,
                                store_id: this.storage.selectedStore ?? (units.length ? units[0].store_id : null)
                            });
                        }
                    }"
                    @lead-created.window="$wire.set('accountView', 'leads')"
                    wire:ignore
                    class="flex flex-col">

                            {{-- Top bar --}}
                            <div class="flex justify-between items-center px-5 py-4 border-surface border-b shrink-0">
                                <div class="flex items-center gap-3">
                                    <button type="button" @click="$wire.set('accountView', 'overview')"
                                        class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                                        <x-heroicon-o-arrow-left class="w-4 h-4" />
                                    </button>
                                    <div>
                                        <p class="font-bold text-fg text-sm leading-tight">New NSA Lead</p>
                                        <p class="text-fg-muted text-xs">Create an NSA sales lead.</p>
                                    </div>
                                </div>
                                {{-- Step indicators --}}
                                <div class="flex items-center font-semibold text-xs">
                                    <template x-for="(s, i) in steps" :key="i">
                                        <div class="flex items-center">
                                            <button
                                                @click="step = i + 1"
                                                :class="step === i + 1
                                                    ? 'bg-surface-3 text-fg border border-surface'
                                                    : (step > i + 1 ? 'text-fuchsia-400 hover:bg-surface-2' : 'text-fg-muted/50 hover:bg-surface-2')"
                                                class="px-3 py-1 rounded font-semibold text-xs transition"
                                                x-text="(i + 1) + '. ' + s">
                                            </button>
                                            <span x-show="i < steps.length - 1" class="mx-0.5 text-fg-muted/30">›</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- Body: Step 1 START --}}
                            <template x-if="step === 1">
                                <div class="flex flex-1 min-h-0 overflow-hidden">
                                    {{-- Left labels --}}
                                    <div class="space-y-8 px-5 py-6 border-surface border-r w-44 shrink-0">
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Contact</p>
                                            <p class="mt-1 text-fg-muted text-xs leading-relaxed">Capture some basic information about the contact.</p>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Needs</p>
                                            <p class="mt-1 text-fg-muted text-xs leading-relaxed">Capture information about the contact's needs for marketing purposes.</p>
                                        </div>
                                    </div>
                                    {{-- Step 1 content --}}
                                    <div class="flex-1 overflow-y-auto">
                                        <div>
                                            <div class="gap-px grid grid-cols-2 border-surface border-b">
                                                <div class="px-6 py-5 border-surface border-r">
                                                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">First Name</label>
                                                    <input type="text" x-model="lead.first_name"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition" />
                                                </div>
                                                <div class="px-6 py-5">
                                                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">Last Name</label>
                                                    <input type="text" x-model="lead.last_name"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition" />
                                                </div>
                                            </div>
                                            <div class="space-y-5 px-6 py-5">
                                                <div>
                                                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">Reason For Storage</label>
                                                    <select x-model="lead.reason_for_storage"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition appearance-none">
                                                        <option value=""></option>
                                                        <option value="Moving">Moving</option>
                                                        <option value="Relocating">Relocating</option>
                                                        <option value="Need Space">Need Space</option>
                                                        <option value="Vehicle">Vehicle</option>
                                                        <option value="Business">Business</option>
                                                        <option value="Between Semesters">Between Semesters</option>
                                                        <option value="Military">Military</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">Types Of Items</label>
                                                    <textarea x-model="lead.types_of_items" rows="2"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition resize-none"></textarea>
                                                    <p class="mt-1 text-fuchsia-400 text-xs">Script: So I can determine the size you need, what types of items will you be storing?</p>
                                                </div>
                                                <div>
                                                    <label class="block mb-1.5 font-medium text-fg-muted text-xs">Duration</label>
                                                    <select x-model="lead.duration"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition appearance-none">
                                                        <option value=""></option>
                                                        <option value="1 Month Or Less">1 Month Or Less</option>
                                                        <option value="2-3 Months">2-3 Months</option>
                                                        <option value="4-6 Months">4-6 Months</option>
                                                        <option value="7-12 Months">7-12 Months</option>
                                                        <option value="Longer Than 1 Year">Longer Than 1 Year</option>
                                                        <option value="Unsure">Unsure</option>
                                                    </select>
                                                    <p class="mt-1 text-fuchsia-400 text-xs">Script: How long do you expect to be storing with us?</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- Body: Step 2 STORAGE --}}
                            <template x-if="step === 2">
                                <div class="flex flex-1 min-h-0 overflow-hidden">

                                    {{-- Stores list --}}
                                    <div class="flex-1 divide-y divide-surface overflow-y-auto">

                                        {{-- Featured store --}}
                                        <template x-for="store in storage.stores.filter(s => s.featured)" :key="store.id">
                                            <div>
                                                {{-- Store header row --}}
                                                <div @click="storage.selectedStore = (storage.selectedStore === store.id ? null : store.id)"
                                                    :class="storage.selectedStore === store.id ? 'bg-surface-2' : 'hover:bg-surface-2'"
                                                    class="flex items-center gap-4 px-4 py-4 transition cursor-pointer">
                                                    <div class="flex justify-center items-center bg-surface-3 rounded-lg w-24 h-16 overflow-hidden shrink-0">
                                                        <x-heroicon-o-building-storefront class="w-8 h-8 text-fg-muted/30" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-fg text-sm truncate" x-text="store.name"></p>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-fg-muted text-xs" x-show="store.type" x-text="store.type"></span>
                                                            <span class="flex items-center gap-1 text-fg-muted text-xs">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><path d="M3 9h18" stroke-width="2"/></svg>
                                                                <span x-text="store.occupancy + '%'"></span>
                                                            </span>
                                                        </div>
                                                        <p class="mt-0.5 text-fg-muted/60 text-xs" x-text="store.address"></p>
                                                        <p class="text-fg-muted/40 text-xs" x-text="store.location"></p>
                                                    </div>
                                                    <div class="flex items-center gap-6 shrink-0">
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.small"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('small') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Small</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.small?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.small?.price"></p>
                                                        </div>
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.medium"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('medium') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Medium</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.medium?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.medium?.price"></p>
                                                        </div>
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.large"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('large') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Large</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.large?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.large?.price"></p>
                                                        </div>
                                                        <x-heroicon-o-chevron-down class="w-4 h-4 text-fg-muted/40 transition-transform shrink-0"
                                                            ::class="storage.selectedStore === store.id ? 'rotate-180' : ''" />
                                                    </div>
                                                </div>

                                                {{-- Unit rows --}}
                                                <div x-show="storage.selectedStore === store.id" x-collapse>
                                                    {{-- Column header --}}
                                                    <div class="flex items-center bg-surface-3/40 px-4 py-2 border-surface border-y font-semibold text-fg-muted text-xs">
                                                        <span class="flex-1">Unit</span>
                                                        <span class="w-24 text-right">Street Rate</span>
                                                        <span class="w-24 text-right">Push Rate</span>
                                                    </div>

                                                    <template x-for="unit in storage.filteredUnits(store.units)" :key="unit.id">
                                                        <div class="px-4 py-4 border-surface/50 border-b last:border-b-0"
                                                            :class="storage.unitQty(store.id, unit.id) > 0 ? 'bg-fuchsia-500/5' : ''">
                                                            <div class="flex items-start gap-4">
                                                                {{-- Selected checkmark --}}
                                                                <div class="flex justify-center items-center mt-1 w-5 h-5 shrink-0">
                                                                    <template x-if="storage.unitQty(store.id, unit.id) > 0">
                                                                        <svg class="w-5 h-5 text-fuchsia-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                                    </template>
                                                                </div>

                                                                {{-- Unit info --}}
                                                                <div class="flex-1 min-w-0">
                                                                    <p class="font-bold text-fg text-base" x-text="unit.size"></p>
                                                                    <p class="mt-0.5 text-fg-muted text-xs">
                                                                        <span x-text="unit.category"></span>
                                                                        <span class="text-fg-muted/50"> (<span x-text="unit.available"></span> units available)</span>
                                                                    </p>

                                                                    {{-- Features --}}
                                                                    <div class="mt-2">
                                                                        <p class="mb-1 font-medium text-fg-muted text-xs">Features</p>
                                                                        <div class="flex flex-wrap gap-1.5">
                                                                            <template x-for="f in unit.features" :key="f">
                                                                                <span class="inline-flex items-center px-2 py-0.5 border border-surface rounded text-fg text-xs" x-text="f"></span>
                                                                            </template>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Rates + controls --}}
                                                                <div class="flex flex-col items-end gap-2 w-48 shrink-0">
                                                                    <div class="flex items-center gap-6 w-full">
                                                                        <div class="flex-1 text-right">
                                                                            <p class="text-fg-muted text-sm line-through" x-text="'$' + unit.street_rate"></p>
                                                                        </div>
                                                                        <div class="w-20 text-right">
                                                                            <p class="font-bold text-fg text-lg" x-text="'$' + unit.push_rate"></p>
                                                                        </div>
                                                                    </div>

                                                                    {{-- Already selected: Remove + qty stepper --}}
                                                                    <template x-if="storage.unitQty(store.id, unit.id) > 0">
                                                                        <div class="flex flex-col items-end gap-2 w-full">
                                                                            <button type="button"
                                                                                @click="storage.setUnitQty(store.id, unit.id, 0)"
                                                                                class="bg-fuchsia-600 hover:bg-fuchsia-500 py-1.5 rounded-lg w-full font-semibold text-white text-xs transition">
                                                                                Remove
                                                                            </button>
                                                                            <div class="flex items-center gap-2">
                                                                                <button type="button"
                                                                                    @click="storage.setUnitQty(store.id, unit.id, Math.max(1, storage.unitQty(store.id, unit.id) - 1))"
                                                                                    class="flex justify-center items-center bg-surface-2 hover:bg-surface-3 rounded w-7 h-7 font-bold text-fg text-base transition">−</button>
                                                                                <span class="w-5 font-semibold text-fg text-sm text-center" x-text="storage.unitQty(store.id, unit.id)"></span>
                                                                                <button type="button"
                                                                                    @click="storage.setUnitQty(store.id, unit.id, storage.unitQty(store.id, unit.id) + 1)"
                                                                                    class="flex justify-center items-center bg-surface-2 hover:bg-surface-3 rounded w-7 h-7 font-bold text-fg text-base transition">+</button>
                                                                            </div>
                                                                        </div>
                                                                    </template>

                                                                    {{-- Not selected: Street Rate Select + Push Rate Select --}}
                                                                    <template x-if="storage.unitQty(store.id, unit.id) === 0">
                                                                        <div class="flex items-center gap-2 w-full">
                                                                            <button type="button"
                                                                                @click="storage.setUnitQty(store.id, unit.id, 1)"
                                                                                class="flex-1 bg-surface-2 hover:bg-surface-3 py-1.5 border border-surface rounded-lg font-semibold text-fg text-xs transition">
                                                                                Select
                                                                            </button>
                                                                            <button type="button"
                                                                                @click="storage.setUnitQty(store.id, unit.id, 1)"
                                                                                class="flex-1 bg-fuchsia-600 hover:bg-fuchsia-500 py-1.5 rounded-lg font-semibold text-white text-xs transition">
                                                                                Select
                                                                            </button>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </div>

                                                            {{-- Promos --}}
                                                            <div class="mt-3 pl-9">
                                                                <p class="mb-1.5 font-medium text-fg-muted text-xs">Promos</p>
                                                                <div class="flex flex-wrap gap-1.5">
                                                                    <template x-for="promo in unit.promos" :key="promo">
                                                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 border border-surface rounded text-fuchsia-400 text-xs">
                                                                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                                                                            <span x-text="promo"></span>
                                                                        </span>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Nearby stores --}}
                                        <div class="bg-surface-3/30 px-4 py-2">
                                            <p class="font-semibold text-fg text-sm">Nearby Stores</p>
                                        </div>

                                        <template x-for="store in storage.stores.filter(s => !s.featured)" :key="store.id">
                                            <div>
                                                <div @click="storage.selectedStore = (storage.selectedStore === store.id ? null : store.id)"
                                                    :class="storage.selectedStore === store.id ? 'bg-surface-2' : 'hover:bg-surface-2'"
                                                    class="flex items-center gap-4 px-4 py-4 transition cursor-pointer">
                                                    <div class="flex justify-center items-center bg-surface-3 rounded-lg w-24 h-16 overflow-hidden shrink-0">
                                                        <x-heroicon-o-building-storefront class="w-8 h-8 text-fg-muted/30" />
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-fg text-sm truncate" x-text="store.name"></p>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <span class="text-fg-muted text-xs" x-show="store.distance" x-text="store.distance + ' miles away'"></span>
                                                            <span class="flex items-center gap-1 text-fg-muted text-xs">
                                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="2" stroke-width="2"/><path d="M3 9h18" stroke-width="2"/></svg>
                                                                <span x-text="store.occupancy + '%'"></span>
                                                            </span>
                                                        </div>
                                                        <p class="mt-0.5 text-fg-muted/60 text-xs" x-text="store.address"></p>
                                                        <p class="text-fg-muted/40 text-xs" x-text="store.location"></p>
                                                    </div>
                                                    <div class="flex items-center gap-6 shrink-0">
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.small"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('small') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Small</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.small?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.small?.price"></p>
                                                        </div>
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.medium"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('medium') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Medium</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.medium?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.medium?.price"></p>
                                                        </div>
                                                        <div class="text-center transition-all"
                                                            x-show="store.pricing.large"
                                                            :class="storage.filters.sizes.length && !storage.filters.sizes.includes('large') ? 'blur-sm opacity-30 select-none' : ''">
                                                            <p class="font-medium text-fg-muted text-xs">Large</p>
                                                            <p class="text-fg-muted/50 text-xs" x-text="store.pricing.large?.size"></p>
                                                            <p class="font-semibold text-fg text-sm" x-text="'$' + store.pricing.large?.price"></p>
                                                        </div>
                                                        <x-heroicon-o-chevron-right class="w-4 h-4 text-fg-muted/40 shrink-0" />
                                                    </div>
                                                </div>
                                            </div>
                                        </template>

                                    </div>

                                    {{-- Filter sidebar --}}
                                    <div class="space-y-5 px-4 py-4 border-surface border-l w-52 overflow-y-auto shrink-0">

                                        {{-- Class --}}
                                        <div>
                                            <p class="mb-2 font-semibold text-fg text-xs">Class</p>
                                            <div class="flex gap-2">
                                                <button type="button" @click="storage.filters.class = 'storage'"
                                                    :class="storage.filters.class === 'storage' ? 'bg-fuchsia-600 text-white' : 'bg-surface-2 text-fg-muted hover:text-fg'"
                                                    class="flex-1 py-1.5 rounded-md font-semibold text-xs transition">Storage</button>
                                                <button type="button" @click="storage.filters.class = 'parking'"
                                                    :class="storage.filters.class === 'parking' ? 'bg-fuchsia-600 text-white' : 'bg-surface-2 text-fg-muted hover:text-fg'"
                                                    class="flex-1 py-1.5 rounded-md font-semibold text-xs transition">Parking</button>
                                            </div>
                                        </div>

                                        {{-- Size --}}
                                        <div>
                                            <p class="mb-2 font-semibold text-fg text-xs">Size</p>
                                            <div class="gap-x-3 gap-y-1.5 grid grid-cols-2">
                                                <template x-for="sz in ['Small', 'Medium', 'Large']" :key="sz">
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" :value="sz.toLowerCase()" x-model="storage.filters.sizes"
                                                            class="w-3.5 h-3.5 accent-fuchsia-500" />
                                                        <span class="text-fg-muted text-xs" x-text="sz"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Popular Dimensions --}}
                                        <div>
                                            <p class="mb-2 font-semibold text-fg text-xs">Popular Dimensions</p>
                                            <div class="gap-x-3 gap-y-1.5 grid grid-cols-2">
                                                <template x-for="dim in ['5 x 5', '5 x 10', '5 x 15', '10 x 10', '10 x 15', '10 x 20', '10 x 25', '10 x 30']" :key="dim">
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" :value="dim" x-model="storage.filters.dimensions"
                                                            class="w-3.5 h-3.5 accent-fuchsia-500" />
                                                        <span class="text-fg-muted text-xs" x-text="dim"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Attributes --}}
                                        <div>
                                            <p class="mb-2 font-semibold text-fg text-xs">Attributes</p>
                                            <div class="space-y-1.5">
                                                <template x-for="attr in ['Inside', 'Outside', 'Temperature Control', 'Drive Up']" :key="attr">
                                                    <label class="flex items-center gap-1.5 cursor-pointer">
                                                        <input type="checkbox" :value="attr" x-model="storage.filters.attributes"
                                                            class="w-3.5 h-3.5 accent-fuchsia-500" />
                                                        <span class="text-fuchsia-400 text-xs" x-text="attr"></span>
                                                    </label>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Max Price --}}
                                        <div>
                                            <p class="mb-2 font-semibold text-fg text-xs">Max Price</p>
                                            <input type="number" x-model="storage.filters.max_price" placeholder=""
                                                class="bg-surface px-3 py-1.5 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-xs transition" />
                                        </div>

                                        {{-- Include Unavailable --}}
                                        <div>
                                            <div class="flex justify-between items-center gap-2 mb-1">
                                                <p class="font-semibold text-fg text-xs">Include Unavailable</p>
                                                <button type="button" @click="storage.filters.include_unavailable = !storage.filters.include_unavailable"
                                                    :class="storage.filters.include_unavailable ? 'bg-fuchsia-600' : 'bg-surface-3'"
                                                    class="inline-flex relative rounded-full w-9 h-5 transition shrink-0">
                                                    <span :class="storage.filters.include_unavailable ? 'translate-x-4' : 'translate-x-0.5'"
                                                        class="inline-block bg-white shadow mt-0.5 rounded-full w-4 h-4 transition-transform"></span>
                                                </button>
                                            </div>
                                            <p class="text-fg-muted/50 text-xs leading-relaxed">Include unavailable units for pricing indication purposes.</p>
                                        </div>

                                    </div>
                                </div>
                            </template>

                            {{-- Body: Step 3 ACTION --}}
                            <template x-if="step === 3">
                                <div class="flex flex-1 min-h-0 overflow-hidden">
                                    <div class="flex-1 overflow-y-auto">

                                        {{-- Lead Type --}}
                                        <div class="flex border-surface border-b">
                                            <div class="px-6 py-6 border-surface border-r w-48 shrink-0">
                                                <p class="font-semibold text-fg text-sm">Lead Type</p>
                                                <p class="mt-1 text-fg-muted text-xs leading-relaxed">Select the type of lead.</p>
                                                <div class="space-y-1 mt-4" x-show="action.type !== ''">
                                                    <p class="font-semibold text-fuchsia-400 text-xs" x-text="action.typeDescriptions[action.type]?.title"></p>
                                                    <p class="text-fg-muted text-xs leading-relaxed" x-text="action.typeDescriptions[action.type]?.body"></p>
                                                    <template x-if="action.type === 'rental'">
                                                        <p class="mt-1 text-fg-muted text-xs leading-relaxed">This brand does not support future date rentals. The move in date must be today.</p>
                                                    </template>
                                                </div>
                                            </div>
                                            <div class="flex-1 px-6 py-6">
                                                <p class="mb-3 font-medium text-fg text-sm">Type</p>
                                                <div class="flex">
                                                    <template x-for="t in ['quote', 'reservation', 'waitlist', 'rental']" :key="t">
                                                        <button type="button" @click="action.type = t"
                                                            :class="action.type === t ? 'bg-fuchsia-600 text-white border-fuchsia-600' : 'bg-surface text-fg-muted hover:text-fg border-surface'"
                                                            class="-ml-px first:ml-0 px-4 py-1.5 border last:rounded-r-lg first:rounded-l-lg font-medium text-sm capitalize transition"
                                                            x-text="t.charAt(0).toUpperCase() + t.slice(1)">
                                                        </button>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Move In Date --}}
                                        <div class="flex border-surface border-b">
                                            <div class="px-6 py-6 border-surface border-r w-48 shrink-0"></div>
                                            <div class="flex-1 px-6 py-6">
                                                <p class="mb-3 font-medium text-fg text-sm">Move In Date</p>
                                                <div class="relative">
                                                    <input type="date" x-model="action.move_in_date"
                                                        :readonly="action.type === 'rental'"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition" />
                                                </div>
                                                <p class="mt-1.5 text-fg-muted/60 text-xs"
                                                    x-text="action.type === 'rental' ? 'The move in date must be today for this brand.' : 'Select the contact\'s estimated move in date.'"></p>
                                            </div>
                                        </div>

                                        {{-- Waitlist: Requirements --}}
                                        <template x-if="action.type === 'waitlist'">
                                            <div class="flex border-surface border-b">
                                                <div class="px-6 py-6 border-surface border-r w-48 shrink-0">
                                                    <p class="font-semibold text-fg text-sm">Requirements</p>
                                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Capture details of the required storage for the waitlist.</p>
                                                </div>
                                                <div class="flex-1 px-6 py-6">
                                                    <p class="mb-3 font-medium text-fg text-sm">Unit Size</p>
                                                    <input type="text" x-model="action.unit_size"
                                                        class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition" />
                                                    <p class="mt-1.5 text-fg-muted/60 text-xs">Enter the dimensions or a brief description of the unit size required.</p>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Notifications (Quote, Reservation, Rental) --}}
                                        <template x-if="action.type !== 'waitlist'">
                                            <div class="flex border-surface border-b">
                                                <div class="px-6 py-6 border-surface border-r w-48 shrink-0">
                                                    <p class="font-semibold text-fg text-sm">Notifications</p>
                                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">
                                                        Select channels to send the customer a confirmation of their
                                                        <span x-text="'self-storage-' + action.type + '.'"></span>
                                                    </p>
                                                </div>
                                                <div class="flex-1 space-y-4 px-6 py-6">
                                                    <div>
                                                        <p class="mb-3 font-medium text-fg text-sm">Channels</p>
                                                        <div class="space-y-2">
                                                            <label class="flex items-center gap-2 cursor-pointer">
                                                                <input type="checkbox" x-model="action.channels.sms" class="w-4 h-4 accent-fuchsia-500" />
                                                                <span class="text-fg-muted text-sm">SMS</span>
                                                            </label>
                                                            <label class="flex items-center gap-2 cursor-pointer">
                                                                <input type="checkbox" x-model="action.channels.email" class="w-4 h-4 accent-fuchsia-500" />
                                                                <span class="text-fg-muted text-sm">Email</span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <template x-if="action.channels.email">
                                                        <div>
                                                            <p class="mb-2 font-medium text-fg text-sm">Email</p>
                                                            <input type="email" x-model="action.email"
                                                                class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition" />
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Adjustments (Quote, Reservation, Rental) --}}
                                        <template x-if="action.type !== 'waitlist'">
                                            <div class="flex border-surface border-b">
                                                <div class="px-6 py-6 border-surface border-r w-48 shrink-0">
                                                    <p class="font-semibold text-fg text-sm">Adjustments</p>
                                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Here you can add promotions and property protection.</p>
                                                    <div class="space-y-1.5 mt-3">
                                                        <a href="#" class="flex items-center gap-1.5 text-fuchsia-400 hover:text-fuchsia-300 text-xs transition">
                                                            <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                                            Property Protection Guide
                                                        </a>
                                                        <a href="#" class="flex items-center gap-1.5 text-fuchsia-400 hover:text-fuchsia-300 text-xs transition">
                                                            <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                                            Promotion Guide
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="flex-1 space-y-5 px-6 py-6">
                                                    {{-- Unit adjustment cards --}}
                                                    <template x-for="(qty, key) in storage.selectedUnits" :key="key">
                                                        <template x-if="qty > 0">
                                                            <div x-data="{
                                                                get info() {
                                                                    const [sId, uId] = key.split('_').map(Number);
                                                                    const store = storage.stores.find(s => s.id === sId);
                                                                    const unit  = store?.units.find(u => u.id === uId);
                                                                    return unit ? { size: unit.size, price: unit.push_rate } : null;
                                                                }
                                                            }" class="space-y-4 bg-surface-2 p-4 border border-surface rounded-xl">
                                                                <template x-if="info">
                                                                    <div>
                                                                        <div class="flex justify-between items-start mb-4">
                                                                            <div>
                                                                                <p class="font-semibold text-fg text-sm" x-text="'Unit (' + info.size + ') @ $' + info.price"></p>
                                                                                <p class="text-fg-muted text-xs">Push Rate</p>
                                                                            </div>
                                                                            <button type="button" @click="storage.setUnitQty(...key.split('_').map(Number), 0)"
                                                                                class="text-fg-muted hover:text-fg transition">
                                                                                <x-heroicon-o-x-mark class="w-4 h-4" />
                                                                            </button>
                                                                        </div>
                                                                        <div class="space-y-3">
                                                                            <div>
                                                                                <p class="mb-1.5 font-semibold text-fg text-xs">Property Protection</p>
                                                                                <select x-model="action.property_protection"
                                                                                    class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition appearance-none">
                                                                                    <option value="">No Coverage</option>
                                                                                    <option value="2000">$2,000 Coverage @ $12.00 Month</option>
                                                                                    <option value="3000">$3,000 Coverage @ $17.00 Month</option>
                                                                                    <option value="5000">$5,000 Coverage @ $25.00 Month</option>
                                                                                </select>
                                                                            </div>
                                                                            <div>
                                                                                <p class="mb-1.5 font-semibold text-fg text-xs">Promo</p>
                                                                                <select x-model="action.promo"
                                                                                    class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition appearance-none">
                                                                                    <option value="-">-</option>
                                                                                    <option value="senior_discount">5% Senior Discount (65+)</option>
                                                                                    <option value="50_off_first">50% Off First Month'S Rent</option>
                                                                                    <option value="1st_month_free">1st Month Free</option>
                                                                                    <option value="military">5% Military & First Responder</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="flex justify-between items-start gap-4 pt-1">
                                                                                <div>
                                                                                    <p class="font-semibold text-fg text-xs">Admin Fee Credit</p>
                                                                                    <p class="mt-0.5 text-fg-muted/60 text-xs leading-relaxed">Apply a credit to offset the administration fee for this rental.</p>
                                                                                </div>
                                                                                <button type="button" @click="action.admin_fee_credit = !action.admin_fee_credit"
                                                                                    :class="action.admin_fee_credit ? 'bg-fuchsia-600' : 'bg-surface-3'"
                                                                                    class="inline-flex relative mt-0.5 rounded-full w-9 h-5 transition shrink-0">
                                                                                    <span :class="action.admin_fee_credit ? 'translate-x-4' : 'translate-x-0.5'"
                                                                                        class="inline-block bg-white shadow mt-0.5 rounded-full w-4 h-4 transition-transform"></span>
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        {{-- Unit Selection (Rental only) --}}
                                        <template x-if="action.type === 'rental'">
                                            <div class="flex border-surface border-b">
                                                <div class="px-6 py-6 border-surface border-r w-48 shrink-0">
                                                    <p class="font-semibold text-fg text-sm">Unit Selection</p>
                                                    <p class="mt-1 text-fg-muted text-xs leading-relaxed">Optionally select a specific unit.</p>
                                                    <a href="#" class="flex items-center gap-1.5 mt-3 text-fuchsia-400 hover:text-fuchsia-300 text-xs transition">
                                                        <x-heroicon-o-information-circle class="w-3.5 h-3.5 shrink-0" />
                                                        Location Map
                                                    </a>
                                                </div>
                                                <div class="flex-1 space-y-4 px-6 py-6">
                                                    <template x-for="(qty, key) in storage.selectedUnits" :key="key">
                                                        <template x-if="qty > 0">
                                                            <div x-data="{
                                                                unit_number: '',
                                                                get info() {
                                                                    const [sId, uId] = key.split('_').map(Number);
                                                                    const store = storage.stores.find(s => s.id === sId);
                                                                    const unit  = store?.units.find(u => u.id === uId);
                                                                    return unit ? { size: unit.size, price: unit.push_rate } : null;
                                                                }
                                                            }">
                                                                <template x-if="info">
                                                                    <div>
                                                                        <p class="mb-2 font-semibold text-fg text-sm" x-text="'Unit (' + info.size + ') @ $' + info.price"></p>
                                                                        <select x-model="unit_number"
                                                                            class="bg-surface px-3 py-2 border border-surface focus:border-fuchsia-500 rounded-lg outline-none w-full text-fg text-sm transition appearance-none">
                                                                            <option value="">Unit Number</option>
                                                                            <option value="2180">Unit Number 2180</option>
                                                                            <option value="2181">Unit Number 2181</option>
                                                                        </select>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </template>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </template>

                            {{-- Body: Step 4 REVIEW --}}
                            <template x-if="step === 4">
                                <div class="flex flex-1 min-h-0 overflow-hidden">

                                    {{-- Left: summary details --}}
                                    <div class="flex-1 divide-y divide-surface overflow-y-auto">

                                        <div class="flex items-center gap-4 px-6 py-4">
                                            <span class="w-40 text-fg-muted text-xs shrink-0">Type</span>
                                            <span class="text-fg text-sm capitalize" x-text="action.type.charAt(0).toUpperCase() + action.type.slice(1)"></span>
                                        </div>

                                        <div class="flex items-center gap-4 px-6 py-4">
                                            <span class="w-40 text-fg-muted text-xs shrink-0">Move In Date</span>
                                            <span class="text-fg text-sm" x-text="action.move_in_date ? new Date(action.move_in_date).toLocaleDateString('en-US', { weekday: 'long', day: 'numeric', month: 'short', year: 'numeric' }) : '—'"></span>
                                        </div>

                                        <div class="flex items-start gap-4 px-6 py-4">
                                            <span class="mt-0.5 w-40 text-fg-muted text-xs shrink-0">Account Strategy</span>
                                            <div class="flex flex-col gap-1">
                                                <div class="flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 text-green-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg>
                                                    <span class="text-fg text-sm">Existing Account</span>
                                                </div>
                                                <span class="text-fg text-sm" x-text="(lead.first_name + ' ' + lead.last_name).trim() || '—'"></span>
                                                <span class="text-fg-muted text-xs">260414231723392</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-4 px-6 py-4">
                                            <span class="w-40 text-fg-muted text-xs shrink-0">Name</span>
                                            <span class="text-fg text-sm" x-text="(lead.first_name + ' ' + lead.last_name).trim() || '—'"></span>
                                        </div>

                                        <div class="flex items-center gap-4 px-6 py-4">
                                            <span class="w-40 text-fg-muted text-xs shrink-0">Email</span>
                                            <span class="text-fuchsia-400 text-sm" x-text="action.email || '—'"></span>
                                        </div>

                                        <div class="flex items-center gap-4 px-6 py-4">
                                            <span class="w-40 text-fg-muted text-xs shrink-0">Notifications</span>
                                            <span class="text-fg text-sm">
                                                <span x-show="action.channels.email && action.channels.sms">Email, SMS</span>
                                                <span x-show="action.channels.email && !action.channels.sms">Email</span>
                                                <span x-show="!action.channels.email && action.channels.sms">SMS</span>
                                                <span x-show="!action.channels.email && !action.channels.sms">None</span>
                                            </span>
                                        </div>

                                    </div>

                                    {{-- Right: Units + Move-In Costs sidebar --}}
                                    <div class="space-y-6 px-5 py-5 border-surface border-l w-72 overflow-y-auto shrink-0">

                                        {{-- Units summary --}}
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Units</p>
                                            <p class="mt-0.5 mb-3 text-fg-muted text-xs">A summary of the selected units.</p>
                                            <div class="space-y-4">
                                                <template x-for="(qty, key) in storage.selectedUnits" :key="key">
                                                    <template x-if="qty > 0">
                                                        <div x-data="{
                                                            get info() {
                                                                const [sId, uId] = key.split('_').map(Number);
                                                                const store = storage.stores.find(s => s.id === sId);
                                                                return store?.units.find(u => u.id === uId) || null;
                                                            },
                                                            get protectionLabel() {
                                                                const map = { '2000': '$2,000 coverage @ $12.00 month', '3000': '$3,000 coverage @ $17.00 month', '5000': '$5,000 coverage @ $25.00 month' };
                                                                return map[action.property_protection] || null;
                                                            },
                                                            get protectionCost() {
                                                                const map = { '2000': 12, '3000': 17, '5000': 25 };
                                                                return map[action.property_protection] || 0;
                                                            },
                                                            get promoLabel() {
                                                                const map = { 'senior_discount': '5% Senior Discount (65+)', '50_off_first': '50% Off First Month\'S Rent', '1st_month_free': '1st Month Free', 'military': '5% Military & First Responder' };
                                                                return action.promo !== '-' ? map[action.promo] : null;
                                                            }
                                                        }">
                                                            <template x-if="info">
                                                                <div>
                                                                    <div class="flex justify-between items-center mb-1">
                                                                        <span class="font-semibold text-fg text-sm">Unit <span x-text="info.id * 100 + info.id"></span></span>
                                                                        <span class="font-bold text-fg text-sm" x-text="'$' + info.push_rate"></span>
                                                                    </div>
                                                                    <div class="flex items-center gap-1.5 mb-1 text-fg-muted text-xs">
                                                                        <x-heroicon-o-archive-box class="w-3.5 h-3.5 shrink-0" />
                                                                        <span x-text="info.size + ' @ Push Rate'"></span>
                                                                    </div>
                                                                    <template x-if="protectionLabel">
                                                                        <div class="flex justify-between items-center mb-1 text-xs">
                                                                            <div class="flex items-center gap-1.5 text-fg-muted">
                                                                                <x-heroicon-o-shield-check class="w-3.5 h-3.5 shrink-0" />
                                                                                <span x-text="protectionLabel"></span>
                                                                            </div>
                                                                            <span class="text-fg-muted" x-text="'+$' + protectionCost"></span>
                                                                        </div>
                                                                    </template>
                                                                    <template x-if="promoLabel">
                                                                        <div class="flex items-center gap-1.5 text-fg-muted text-xs">
                                                                            <x-heroicon-o-tag class="w-3.5 h-3.5 shrink-0" />
                                                                            <span x-text="promoLabel"></span>
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </template>
                                                </template>
                                            </div>
                                        </div>

                                        {{-- Move-In Costs --}}
                                        <div>
                                            <p class="font-semibold text-fg text-sm">Move-In Costs</p>
                                            <p class="mt-0.5 mb-4 text-fg-muted text-xs">The charges payable at move-in.</p>
                                            <div x-data="{
                                                get unitCost() {
                                                    return Object.entries(storage.selectedUnits).reduce((sum, [key, qty]) => {
                                                        if (!qty) return sum;
                                                        const [sId, uId] = key.split('_').map(Number);
                                                        const store = storage.stores.find(s => s.id === sId);
                                                        const unit = store?.units.find(u => u.id === uId);
                                                        return sum + (unit ? unit.push_rate * qty : 0);
                                                    }, 0);
                                                },
                                                get protectionCost() {
                                                    const map = { '2000': 12, '3000': 17, '5000': 25 };
                                                    return map[action.property_protection] || 0;
                                                },
                                                get discount() {
                                                    return action.promo === 'senior_discount' ? -(this.unitCost * 0.05).toFixed(2) :
                                                           action.promo === 'military'        ? -(this.unitCost * 0.05).toFixed(2) :
                                                           action.promo === '50_off_first'    ? -(this.unitCost * 0.50).toFixed(2) :
                                                           action.promo === '1st_month_free'  ? -this.unitCost : 0;
                                                },
                                                get adminFee() { return 29; },
                                                get subtotal() { return (Number(this.unitCost) + Number(this.protectionCost) + Number(this.adminFee) + Number(this.discount)); },
                                                get tax() { return (this.subtotal * 0.008).toFixed(2); },
                                                get total() { return (Number(this.subtotal) + Number(this.tax)).toFixed(2); }
                                            }" class="space-y-3">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-medium text-fg text-xs">Move In Rent</p>
                                                        <p class="text-fg-muted/50 text-xs">Move In Rent</p>
                                                    </div>
                                                    <span class="font-semibold text-fg text-xs" x-text="'$' + Number(unitCost).toFixed(2)"></span>
                                                </div>
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-medium text-fg text-xs">Administrative Fee</p>
                                                        <p class="text-fg-muted/50 text-xs">Administrative Fee</p>
                                                    </div>
                                                    <span class="font-semibold text-fg text-xs" x-text="'$' + adminFee"></span>
                                                </div>
                                                <template x-if="protectionCost > 0">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <p class="font-medium text-fg text-xs">Insurance</p>
                                                            <p class="text-fg-muted/50 text-xs">Insurance</p>
                                                        </div>
                                                        <span class="font-semibold text-fg text-xs" x-text="'$' + protectionCost"></span>
                                                    </div>
                                                </template>
                                                <template x-if="discount != 0">
                                                    <div class="flex justify-between items-start">
                                                        <div>
                                                            <p class="font-medium text-fg text-xs italic">Discount</p>
                                                            <p class="text-fg-muted/50 text-xs">Discount</p>
                                                        </div>
                                                        <span class="font-semibold text-xs text-accent-red" x-text="'$' + Number(discount).toFixed(2)"></span>
                                                    </div>
                                                </template>
                                                <div class="space-y-1 pt-3 border-surface border-t">
                                                    <div class="flex justify-between items-center">
                                                        <p class="font-semibold text-fg text-sm">Total Move In Cost</p>
                                                        <span class="font-bold text-fg text-base" x-text="'$' + total"></span>
                                                    </div>
                                                    <div class="flex justify-between items-center">
                                                        <p class="text-fg-muted text-xs">Tax Included</p>
                                                        <span class="text-fg-muted text-xs" x-text="'$' + tax"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </template>

                            {{-- Footer --}}
                            <div class="border-surface border-t shrink-0">

                                {{-- Selected units summary (step 2 only) --}}
                                <template x-if="(step === 2 || step === 3 || step === 4) && Object.keys(storage.selectedUnits).length > 0">
                                    <div class="flex flex-wrap items-center gap-3 bg-surface-2 px-5 py-2 border-surface border-b">
                                        <template x-for="(qty, key) in storage.selectedUnits" :key="key">
                                            <template x-if="qty > 0">
                                                <div x-data="{
                                                    get info() {
                                                        const [sId, uId] = key.split('_').map(Number);
                                                        const store = storage.stores.find(s => s.id === sId);
                                                        const unit  = store?.units.find(u => u.id === uId);
                                                        return unit ? { size: unit.size, price: unit.push_rate, qty } : null;
                                                    }
                                                }" class="flex items-center gap-1.5">
                                                    <template x-if="info">
                                                        <span class="inline-flex items-center gap-1 bg-fuchsia-500/10 px-2.5 py-1 border border-fuchsia-500/30 rounded-full text-fg text-xs">
                                                            <span class="font-semibold text-fuchsia-400" x-text="info.qty + ' ×'"></span>
                                                            <span x-text="info.size"></span>
                                                            <span class="text-fg-muted">@</span>
                                                            <span class="font-semibold" x-text="'$' + info.price"></span>
                                                            <span class="text-fg-muted/60">Push Rate</span>
                                                        </span>
                                                    </template>
                                                </div>
                                            </template>
                                        </template>
                                        <span class="ml-auto font-medium text-fg-muted text-xs"
                                            x-text="Object.values(storage.selectedUnits).reduce((a,b)=>a+b,0) + ' unit' + (Object.values(storage.selectedUnits).reduce((a,b)=>a+b,0) === 1 ? '' : 's') + ' selected'">
                                        </span>
                                    </div>
                                </template>

                                <div class="flex justify-between items-center px-5 py-3">
                                    <button type="button" x-show="step > 1" @click="step--"
                                        class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface-3 px-4 py-2 rounded-lg font-semibold text-fg-muted hover:text-fg text-sm transition">
                                        Back
                                    </button>
                                    <span x-show="step === 1"></span>
                                    <button type="button"
                                        @click="step === 4 ? submitLead($wire) : (step < steps.length ? step++ : null)"
                                        :disabled="step > steps.length"
                                        class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 disabled:opacity-50 px-4 py-2 rounded-lg font-semibold text-white text-sm transition disabled:cursor-not-allowed"
                                        x-text="step === 4 ? ('Create ' + action.type.charAt(0).toUpperCase() + action.type.slice(1)) : 'Continue'">
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
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
        }" @note-added.window="note = ''; sending = false; saved = true; setTimeout(() => saved = false, 2500)">

            {{-- Composer header row --}}
            <div class="flex justify-between items-center px-4 pt-3 pb-1.5">
                <div class="flex items-center gap-1.5">
                    <span class="inline-flex items-center gap-1.5 bg-fuchsia-500/10 px-2.5 py-1 border border-fuchsia-500/20 rounded-md font-semibold text-fuchsia-400 text-xs">
                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                        Note
                    </span>
                </div>
                {{-- Saved indicator --}}
                <span x-show="saved" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                    class="inline-flex items-center gap-1.5 font-medium text-xs text-accent-green">
                    <x-heroicon-o-check-circle class="w-3.5 h-3.5" />
                    Note saved
                </span>
            </div>

            {{-- Textarea --}}
            <div class="px-4 py-2">
                <textarea x-ref="noteInput" x-model="note" placeholder="Type your note…" rows="3"
                    :disabled="sending"
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
                    :class="note.trim() && !sending
                        ? 'bg-fuchsia-600 hover:bg-fuchsia-500 text-white cursor-pointer'
                        : 'bg-surface-2 text-fg-muted cursor-not-allowed opacity-60'"
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
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
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
                            <dd class="font-medium text-fg text-xs text-right">{{ $conversation->contact_name ?? '—' }}</dd>
                        </div>
                        @if ($conversation->contact_phone)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Phone</dt>
                            <dd class="font-mono font-medium text-fg text-xs text-right">{{ $conversation->contact_phone }}</dd>
                        </div>
                        @endif
                        @if ($conversation->campaign)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Campaign</dt>
                            <dd class="font-medium text-fg text-xs text-right">{{ $conversation->campaign->name }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-28 text-fg-muted text-xs shrink-0">Leads</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                @if ($leads->count() > 0)
                                    <span class="inline-flex items-center bg-fuchsia-500/15 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">{{ $leads->count() }} lead{{ $leads->count() === 1 ? '' : 's' }}</span>
                                @else
                                    <span class="text-fg-muted">None</span>
                                @endif
                            </dd>
                        </div>
                        @if ($firstLead)
                        <div class="space-y-3 pt-3 border-surface border-t">
                            <dt class="font-semibold text-fg-muted text-xs uppercase tracking-wide">Latest Lead</dt>
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Type</dt>
                                <dd>
                                    <span class="inline-flex items-center bg-fuchsia-500/20 px-2 py-0.5 rounded-full font-semibold text-fuchsia-300 text-xs">
                                        {{ ucfirst($firstLead->lead_type ?? '—') }}
                                    </span>
                                </dd>
                            </div>
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Status</dt>
                                <dd>
                                    <span class="inline-flex items-center bg-yellow-400/20 px-2 py-0.5 rounded-full font-semibold text-yellow-300 text-xs">
                                        {{ ucfirst(strtolower($firstLead->status ?? 'New')) }}
                                    </span>
                                </dd>
                            </div>
                            @if ($firstLead->store)
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Store</dt>
                                <dd class="font-medium text-fuchsia-400 text-xs text-right">{{ $firstLead->store->name }}</dd>
                            </div>
                            @endif
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-28 text-fg-muted text-xs shrink-0">Created</dt>
                                <dd class="text-fg text-xs text-right">{{ $firstLead->created_at->format('j M Y') }}</dd>
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
                                <dd class="font-medium text-fg text-xs text-right">{{ $accountStore->address }}</dd>
                            </div>
                            @endif
                            @if ($accountStore->city || $accountStore->state)
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-20 text-fg-muted text-xs shrink-0">Location</dt>
                                <dd class="font-medium text-fg text-xs text-right">{{ implode(', ', array_filter([$accountStore->city, $accountStore->state, $accountStore->zip])) }}</dd>
                            </div>
                            @endif
                            @if (isset($accountStore->occupancy))
                            <div class="flex justify-between items-start gap-2">
                                <dt class="w-20 text-fg-muted text-xs shrink-0">Occupancy</dt>
                                <dd class="font-medium text-fg text-xs text-right">{{ $accountStore->occupancy }}%</dd>
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
                                <dd class="font-medium text-fg text-xs text-right capitalize">{{ $accountStore->type }}</dd>
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
