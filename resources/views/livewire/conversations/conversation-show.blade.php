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
    _fn: null,
    init() {
        this._fn = () => { this.$el.style.height = (window.innerHeight - this.$el.getBoundingClientRect().top - 24) + 'px'; };
        this._fn();
        window.addEventListener('resize', this._fn);
    },
    destroy() { window.removeEventListener('resize', this._fn); }
}" class="flex bg-surface border border-surface rounded-xl [overflow:clip]" wire:poll.10s>

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

            {{-- Action icons row --}}
            <div class="flex items-center gap-0.5 mt-3">
                <button type="button" title="Assign agent"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <x-heroicon-o-user-plus class="w-4 h-4" />
                </button>
                <button type="button" title="Transfer"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <x-heroicon-o-arrows-right-left class="w-4 h-4" />
                </button>
                <button type="button" title="Tag"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <x-heroicon-o-tag class="w-4 h-4" />
                </button>
                <button type="button" title="More options"
                    class="flex justify-center items-center hover:bg-surface-2 rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                    <x-heroicon-o-ellipsis-horizontal class="w-4 h-4" />
                </button>
                <a href="{{ route('conversations.index') }}" wire:navigate
                    class="flex items-center gap-1.5 ml-auto text-fg-muted hover:text-fg text-xs transition">
                    <x-heroicon-o-arrow-left class="w-3.5 h-3.5" />
                    All conversations
                </a>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex items-center gap-0 bg-surface-3/20 px-5 border-surface border-b shrink-0">
            @foreach (['timeline' => 'Timeline', 'account' => 'Account'] as $tabKey => $tabLabel)
                <button type="button" wire:click="$set('activeTab', '{{ $tabKey }}')"
                    class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition
                        {{ $activeTab === $tabKey ? 'border-fuchsia-500 text-fg' : 'border-transparent text-fg-muted hover:text-fg' }}">
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>

        {{-- Scrollable content area --}}
        <div class="flex-1 overflow-y-auto">
            @if ($activeTab === 'timeline')
                <div class="space-y-7 p-6">

                    {{-- System: initial status event --}}
                    <div class="flex items-start gap-3">
                        <div
                            class="flex justify-center items-center bg-fuchsia-500/15 mt-0.5 rounded-full w-7 h-7 shrink-0">
                            <x-heroicon-o-bolt class="w-3.5 h-3.5 text-fuchsia-400" />
                        </div>
                        <div class="flex-1 pt-1 min-w-0">
                            <p class="text-fg-muted text-xs">
                                <span class="font-semibold text-fuchsia-400">System</span>
                                set the initial status to
                                <span
                                    class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                            </p>
                            <p class="mt-0.5 text-fg-muted/50 text-xs">
                                {{ ($conversation->started_at ?? $conversation->created_at)->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    {{-- Call / channel event --}}
                    <div class="flex items-start gap-3">
                        <div
                            class="shrink-0 w-7 h-7 rounded-full {{ $contactColor }} flex items-center justify-center font-bold text-white text-xs mt-0.5">
                            {{ $conversation->initials }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-2.5">
                                <p class="text-fg text-sm">
                                    <span class="font-semibold">{{ $conversation->contact_name ?? 'Unknown' }}</span>
                                    made an {{ $conversation->direction }} {{ $conversation->channel }}
                                </p>
                                @if ($conversation->channel === 'voice')
                                    <x-heroicon-s-phone class="w-3.5 h-3.5 text-fg-muted/60 shrink-0" />
                                @elseif ($conversation->channel === 'sms')
                                    <x-heroicon-s-chat-bubble-left-ellipsis
                                        class="w-3.5 h-3.5 text-fg-muted/60 shrink-0" />
                                @elseif ($conversation->channel === 'email')
                                    <x-heroicon-s-envelope class="w-3.5 h-3.5 text-fg-muted/60 shrink-0" />
                                @else
                                    <x-heroicon-s-chat-bubble-oval-left class="w-3.5 h-3.5 text-fg-muted/60 shrink-0" />
                                @endif
                                <span class="ml-auto text-fg-muted/50 text-xs whitespace-nowrap shrink-0">
                                    {{ ($conversation->started_at ?? $conversation->created_at)->diffForHumans() }}
                                </span>
                            </div>

                            {{-- Event detail card --}}
                            <div class="bg-surface-2 border border-surface rounded-xl overflow-hidden">
                                <div class="flex justify-between items-center px-4 py-3 border-surface border-b">
                                    <div class="flex items-center gap-2">
                                        @if ($conversation->channel === 'voice')
                                            <x-heroicon-o-phone class="w-4 h-4 text-fg-muted" />
                                        @elseif ($conversation->channel === 'sms')
                                            <x-heroicon-o-chat-bubble-left-ellipsis class="w-4 h-4 text-fg-muted" />
                                        @elseif ($conversation->channel === 'email')
                                            <x-heroicon-o-envelope class="w-4 h-4 text-fg-muted" />
                                        @else
                                            <x-heroicon-o-chat-bubble-oval-left class="w-4 h-4 text-fg-muted" />
                                        @endif
                                        <span class="font-semibold text-fg text-sm">{{ $channelTitle }}</span>
                                    </div>
                                    <span class="font-mono text-fg-muted text-xs"># {{ $conversation->id }}</span>
                                </div>
                                <div class="divide-y divide-surface">
                                    @if ($conversation->contact_phone)
                                        <div class="flex items-center gap-4 px-4 py-2.5">
                                            <span class="w-24 text-fg-muted text-xs shrink-0">From</span>
                                            <span
                                                class="font-mono text-fg text-sm">{{ $conversation->contact_phone }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-4 px-4 py-2.5">
                                        <span class="w-24 text-fg-muted text-xs shrink-0">Status</span>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold text-xs {{ $s['class'] }}">
                                            {{ $s['label'] }}
                                        </span>
                                    </div>
                                    @if ($conversation->duration_seconds !== null)
                                        <div class="flex items-center gap-4 px-4 py-2.5">
                                            <span class="w-24 text-fg-muted text-xs shrink-0">Duration</span>
                                            <span
                                                class="font-mono text-fg text-sm">{{ $conversation->duration_label }}</span>
                                        </div>
                                    @endif
                                    <div class="flex items-center gap-4 px-4 py-2.5">
                                        <span class="w-24 text-fg-muted text-xs shrink-0">Answered</span>
                                        <span class="text-fg text-sm">
                                            {{ in_array($conversation->status, ['in_progress', 'completed']) ? 'Yes' : 'No' }}
                                        </span>
                                    </div>
                                    @if ($conversation->detail_preview)
                                        <div class="flex items-start gap-4 px-4 py-2.5">
                                            <span class="mt-0.5 w-24 text-fg-muted text-xs shrink-0">Preview</span>
                                            <span
                                                class="text-fg-muted text-sm">{{ $conversation->detail_preview }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notes --}}
                    @foreach ($notes as $note)
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
                                        {{ $note->content }}</div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                </div>
            @else
                {{-- Account tab placeholder --}}
                <div class="flex flex-col justify-center items-center py-20 h-full text-center">
                    <x-heroicon-o-user-circle class="mb-3 w-10 h-10 text-fg-muted/30" />
                    <p class="text-fg-muted text-sm">No account linked.</p>
                    <p class="mt-1 text-fg-muted/50 text-xs">Link a contact to this conversation to view account
                        details.</p>
                </div>
            @endif
        </div>

        {{-- Note composer --}}
        <div class="bg-surface-3/20 border-surface border-t shrink-0" x-data="{
            note: '',
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
                if (!content) return;
                $wire.call('addNote', content);
                this.note = '';
                $refs.noteInput.value = '';
            }
        }">
            {{-- "Note" tab label --}}
            <div class="px-4 pt-3 pb-1">
                <span
                    class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 border border-surface rounded-md font-semibold text-fg text-xs">
                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                    Note
                </span>
            </div>

            {{-- Textarea --}}
            <div class="px-4 py-2">
                <textarea x-ref="noteInput" x-model="note" placeholder="Type your note..." rows="3"
                    class="bg-transparent outline-none w-full text-fg placeholder:text-fg-muted/40 text-sm leading-relaxed resize-none"></textarea>
            </div>

            {{-- Toolbar --}}
            <div class="flex justify-between items-center px-3 pt-2 pb-3 border-surface/50 border-t">
                <div class="flex items-center gap-0.5">
                    <button type="button" @click="fmt('**', '**')"
                        class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 font-bold text-fg-muted hover:text-fg text-xs transition">B</button>
                    <button type="button" @click="fmt('*', '*')"
                        class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 font-serif text-fg-muted hover:text-fg text-xs italic transition">I</button>
                    <button type="button" @click="fmt('__', '__')"
                        class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg text-xs underline transition">U</button>
                    <button type="button" @click="fmt('\`', '\`')" title="Code"
                        class="flex justify-center items-center hover:bg-surface-2 rounded w-6 h-6 text-fg-muted hover:text-fg transition">
                        <x-heroicon-o-code-bracket class="w-3.5 h-3.5" />
                    </button>
                    <div class="bg-surface-2 mx-1 w-px h-4"></div>
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
                <button type="button" @click="submit()" :disabled="!note.trim()"
                    :class="note.trim() ?
                        'bg-fuchsia-600 hover:bg-fuchsia-500 text-white cursor-pointer' :
                        'bg-surface-2 text-fg-muted cursor-not-allowed'"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg font-semibold text-xs transition">
                    Add Note
                </button>
            </div>
        </div>
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
            @foreach (['details' => 'Details', 'account' => 'Account', 'store' => 'Store'] as $key => $label)
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
                    @if ($conversation->campaign)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Brand</dt>
                            <dd class="font-medium text-fg text-xs text-right">{{ $conversation->campaign->name }}
                            </dd>
                        </div>
                    @endif
                    @if ($conversation->queue)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Queue</dt>
                            <dd class="font-medium text-fg text-xs text-right">{{ $conversation->queue }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between items-start gap-2">
                        <dt class="w-24 text-fg-muted text-xs shrink-0">Channel</dt>
                        <dd class="font-medium text-fg text-xs text-right capitalize">{{ $conversation->channel }}
                        </dd>
                    </div>
                    <div class="flex justify-between items-start gap-2">
                        <dt class="w-24 text-fg-muted text-xs shrink-0">Direction</dt>
                        <dd class="font-medium text-fg text-xs text-right capitalize">{{ $conversation->direction }}
                        </dd>
                    </div>
                    @if ($conversation->assignedAgent)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Assigned To</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->assignedAgent->first_name }}
                                {{ $conversation->assignedAgent->last_name }}</dd>
                        </div>
                    @endif
                    @if ($conversation->completedByAgent)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Completed By</dt>
                            <dd class="font-medium text-fg text-xs text-right">
                                {{ $conversation->completedByAgent->first_name }}
                                {{ $conversation->completedByAgent->last_name }}</dd>
                        </div>
                    @endif
                    @if ($conversation->duration_label)
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Duration</dt>
                            <dd class="font-mono font-medium text-fg text-xs text-right">
                                {{ $conversation->duration_label }}</dd>
                        </div>
                    @endif
                    <div class="space-y-3 pt-3 border-surface border-t">
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Created At</dt>
                            <dd class="text-fg text-xs text-right">
                                {{ $conversation->created_at->format('j M Y g:ia') }}</dd>
                        </div>
                        <div class="flex justify-between items-start gap-2">
                            <dt class="w-24 text-fg-muted text-xs shrink-0">Updated At</dt>
                            <dd class="text-fg text-xs text-right">
                                {{ $conversation->updated_at->format('j M Y g:ia') }}</dd>
                        </div>
                    </div>
                </dl>
            @elseif ($sidebarTab === 'account')
                <div class="flex flex-col justify-center items-center py-12 text-center">
                    <x-heroicon-o-user class="mb-2 w-8 h-8 text-fg-muted/30" />
                    <p class="text-fg-muted text-xs">No account linked.</p>
                </div>
            @else
                <div class="flex flex-col justify-center items-center py-12 text-center">
                    <x-heroicon-o-building-storefront class="mb-2 w-8 h-8 text-fg-muted/30" />
                    <p class="text-fg-muted text-xs">No store linked.</p>
                </div>
            @endif
        </div>
    </div>

</div>
