<div class="space-y-6">

    {{-- ── Page Header ──────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-fg-muted text-xs mb-1">
                <a href="{{ route('campaigns.index') }}" wire:navigate class="hover:text-fg transition">Campaigns</a>
                <span>/</span>
                <span class="text-fg">{{ $name ?: 'Edit Campaign' }}</span>
            </nav>
            <h1 class="font-bold text-fg text-2xl leading-tight">Edit Campaign</h1>
            <p class="text-fg-muted text-sm mt-0.5">Update campaign and dialer settings.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('campaign.lists', $campaign) }}" wire:navigate
                class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface border border-surface px-3 py-2 rounded-lg text-fg text-sm font-medium transition">
                <x-heroicon-o-queue-list class="w-4 h-4 text-fg-muted" />
                Call Lists
            </a>
            <a href="{{ route('campaign.dispositions', $campaign) }}" wire:navigate
                class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface border border-surface px-3 py-2 rounded-lg text-fg text-sm font-medium transition">
                <x-heroicon-o-tag class="w-4 h-4 text-fg-muted" />
                Dispositions
            </a>
            @can('campaign.delete')
                <button wire:click="confirmDelete" type="button"
                    class="inline-flex items-center gap-1.5 bg-red-500/10 hover:bg-red-500/20 border border-red-500/30 px-3 py-2 rounded-lg text-accent-red text-sm font-medium transition">
                    <x-heroicon-o-trash class="w-4 h-4" />
                    Delete
                </button>
            @endcan
        </div>
    </div>

    {{-- ── Flash messages ──────────────────────────────────────────────── --}}
    @if (session('success'))
        <div
            class="flex items-center gap-2 bg-green-500/10 border border-green-500/30 rounded-xl px-4 py-3 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Delete confirmation ──────────────────────────────────────────── --}}
    @if ($confirmingDelete)
        <div
            class="flex items-center justify-between gap-4 bg-red-500/10 border border-red-500/30 rounded-xl px-4 py-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-accent-red shrink-0" />
                <p class="text-accent-red text-sm">Delete <strong>{{ $campaign->name }}</strong>? This cannot be undone.
                </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="delete" type="button"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-semibold transition">
                    Yes, delete
                </button>
                <button wire:click="$set('confirmingDelete', false)" type="button"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-surface-2 hover:bg-surface border border-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </button>
            </div>
        </div>
    @endif

    {{-- ── Form ─────────────────────────────────────────────────────────── --}}
    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- ── Left: main settings (2/3) ── --}}
            <div class="xl:col-span-2 space-y-5">

                {{-- Campaign Details --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-fuchsia-500/15 shrink-0">
                            <x-heroicon-s-megaphone class="w-3.5 h-3.5 text-fuchsia-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Campaign Details</h2>
                    </div>
                    <div class="p-5 space-y-4">

                        <div>
                            <label class="block mb-1.5 font-semibold text-fg text-xs">
                                Campaign Name <span class="text-accent-red">*</span>
                            </label>
                            <input wire:model.defer="name" type="text" placeholder="e.g. Sales Q2 2026"
                                class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                            @error('name')
                                <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Inbound Phone Number</label>
                                <input wire:model.defer="phone_number" type="text" placeholder="+10000000000"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                <p class="text-fg-muted/60 text-xs mt-1">The number callers dial to reach this campaign
                                    (inbound DID matching).</p>
                                @error('phone_number')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Description</label>
                                <textarea wire:model.defer="description" rows="3" placeholder="Optional description..."
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-fuchsia-500"></textarea>
                                @error('description')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <label class="flex items-center gap-2.5 cursor-pointer w-fit">
                            <input wire:model.defer="is_active" type="checkbox" id="is_active"
                                class="w-4 h-4 rounded text-fuchsia-600 border-surface-2 focus:ring-fuchsia-500 focus:ring-offset-0">
                            <span class="text-fg text-sm font-medium">Active</span>
                            <span class="text-fg-muted text-xs">(campaign accepts calls)</span>
                        </label>
                    </div>
                </div>

                {{-- Dialer Settings --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-blue-500/15 shrink-0">
                            <x-heroicon-s-phone class="w-3.5 h-3.5 text-blue-400" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Dialer Settings</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">Configure how calls are placed and
                            handled</p>
                    </div>
                    <div class="p-5 space-y-5">

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Campaign Type</label>
                                <select wire:model.defer="type"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="OUTBOUND">Outbound</option>
                                    <option value="INBOUND">Inbound</option>
                                    <option value="BLENDED">Blended</option>
                                </select>
                                @error('type')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Dial Mode</label>
                                <select wire:model.defer="dial_mode"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                    <option value="MANUAL">Manual</option>
                                    <option value="PREVIEW">Preview</option>
                                    <option value="PROGRESSIVE">Progressive</option>
                                    <option value="PREDICTIVE">Predictive</option>
                                </select>
                                @error('dial_mode')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">
                                    Dial Level
                                    <span class="font-normal text-fg-muted">(predictive ratio)</span>
                                </label>
                                <input wire:model.defer="dial_level" type="number" step="0.1" min="0.1"
                                    max="10"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('dial_level')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Max Simultaneous
                                    Calls</label>
                                <input wire:model.defer="max_calls" type="number" min="1" max="100"
                                    placeholder="Unlimited"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('max_calls')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">Outbound Caller ID</label>
                                <input wire:model.defer="caller_id" type="text"
                                    placeholder="+1234567890 (blank = system default)"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('caller_id')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block mb-1.5 font-semibold text-fg text-xs">
                                    Hopper Level
                                    <span class="font-normal text-fg-muted">(leads to pre-queue)</span>
                                </label>
                                <input wire:model.defer="hopper_level" type="number" min="1" max="1000"
                                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                @error('hopper_level')
                                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="block mb-1.5 font-semibold text-fg text-xs">
                                After-Call Work (ACW) Timer
                                <span class="font-normal text-fg-muted">— seconds, 0 = disabled</span>
                            </label>
                            <div class="flex items-center gap-3">
                                <input wire:model.defer="acw_seconds" type="number" min="0" max="3600"
                                    class="w-28 bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                                <span class="text-fg-muted text-xs">seconds of wrap-up time after each call</span>
                            </div>
                            @error('acw_seconds')
                                <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- Agent Script --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="flex items-center gap-2.5 px-5 py-4 border-b border-surface bg-surface-2">
                        <div class="flex items-center justify-center w-7 h-7 rounded-full bg-yellow-500/15 shrink-0">
                            <x-heroicon-s-document-text class="w-3.5 h-3.5 text-accent-yellow" />
                        </div>
                        <h2 class="font-semibold text-fg text-sm">Agent Script</h2>
                        <p class="text-fg-muted text-xs ml-auto hidden sm:block">Shown to agents during active calls
                        </p>
                    </div>
                    <div class="p-5">
                        <textarea wire:model.defer="script" rows="8" placeholder="Enter the script agents will see during calls..."
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg placeholder:text-fg-muted/40 text-sm font-mono resize-y focus:outline-none focus:ring-2 focus:ring-fuchsia-500"></textarea>
                        <p class="text-fg-muted/60 text-xs mt-1.5">Supports plain text. Displayed in a scrollable
                            Script tab in the conversation view.</p>
                        @error('script')
                            <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-5 py-2.5 rounded-lg font-semibold text-white text-sm transition">
                        <span wire:loading.remove wire:target="save">Save Changes</span>
                        <span wire:loading wire:target="save">Saving…</span>
                    </button>
                    <a href="{{ route('campaigns.index') }}" wire:navigate
                        class="inline-flex items-center px-4 py-2.5 rounded-lg border border-surface bg-surface-2 hover:bg-surface text-fg-muted hover:text-fg text-sm font-medium transition">
                        Cancel
                    </a>
                </div>

            </div>

            {{-- ── Right: sidebar (1/3) ── --}}
            <div class="space-y-4">

                {{-- Summary card --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-surface-2 border-b border-surface">
                        <h3 class="font-semibold text-fg text-sm">Summary</h3>
                    </div>
                    <dl class="divide-y divide-surface">
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">ID</dt>
                            <dd class="font-mono text-fg text-xs">#{{ $campaign->id }}</dd>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">Type</dt>
                            <dd>
                                @php
                                    $tColor = [
                                        'OUTBOUND' => 'text-blue-400',
                                        'INBOUND' => 'text-accent-green',
                                        'BLENDED' => 'text-fuchsia-400',
                                    ];
                                @endphp
                                <span
                                    class="font-semibold text-xs {{ $tColor[$campaign->type] ?? 'text-fg-muted' }}">{{ $campaign->type }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">Dial Mode</dt>
                            <dd class="font-mono text-fg text-xs">{{ $campaign->dial_mode }}</dd>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">Status</dt>
                            <dd>
                                @if ($campaign->is_active)
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-500/15 text-accent-green text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-accent-green inline-block"></span>
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-surface-2 text-fg-muted text-xs font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-fg-muted/40 inline-block"></span>
                                        Inactive
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">Created</dt>
                            <dd class="text-fg text-xs">{{ $campaign->created_at->format('j M Y') }}</dd>
                        </div>
                        <div class="flex items-center justify-between px-4 py-2.5">
                            <dt class="text-fg-muted text-xs">Updated</dt>
                            <dd class="text-fg text-xs">{{ $campaign->updated_at->diffForHumans() }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Quick links --}}
                <div class="bg-surface border border-surface rounded-xl overflow-hidden">
                    <div class="px-4 py-3 bg-surface-2 border-b border-surface">
                        <h3 class="font-semibold text-fg text-sm">Quick Links</h3>
                    </div>
                    <div class="divide-y divide-surface">
                        <a href="{{ route('campaign.lists', $campaign) }}" wire:navigate
                            class="flex items-center justify-between px-4 py-3 hover:bg-surface-2 transition group">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-queue-list class="w-4 h-4 text-fuchsia-400 shrink-0" />
                                <span class="text-fg text-sm font-medium">Call Lists</span>
                            </div>
                            <x-heroicon-o-chevron-right
                                class="w-3.5 h-3.5 text-fg-muted/40 group-hover:text-fg-muted transition" />
                        </a>
                        <a href="{{ route('campaign.dispositions', $campaign) }}" wire:navigate
                            class="flex items-center justify-between px-4 py-3 hover:bg-surface-2 transition group">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-tag class="w-4 h-4 text-fuchsia-400 shrink-0" />
                                <span class="text-fg text-sm font-medium">Dispositions</span>
                            </div>
                            <x-heroicon-o-chevron-right
                                class="w-3.5 h-3.5 text-fg-muted/40 group-hover:text-fg-muted transition" />
                        </a>
                        <a href="{{ route('conversations.index') }}" wire:navigate
                            class="flex items-center justify-between px-4 py-3 hover:bg-surface-2 transition group">
                            <div class="flex items-center gap-2.5">
                                <x-heroicon-o-chat-bubble-left-right class="w-4 h-4 text-fuchsia-400 shrink-0" />
                                <span class="text-fg text-sm font-medium">Conversations</span>
                            </div>
                            <x-heroicon-o-chevron-right
                                class="w-3.5 h-3.5 text-fg-muted/40 group-hover:text-fg-muted transition" />
                        </a>
                    </div>
                </div>

                {{-- Dial mode guide --}}
                <div class="bg-surface border border-surface rounded-xl p-4 space-y-3">
                    <h3 class="font-semibold text-fg-muted text-xs uppercase tracking-wide">Dial Mode Guide</h3>
                    <div class="space-y-2">
                        @foreach ([
        'MANUAL' => 'Agent enters number manually each call.',
        'PREVIEW' => 'Agent reviews lead info before dialing.',
        'PROGRESSIVE' => 'Server dials one lead per available agent.',
        'PREDICTIVE' => 'Server dials by ratio to maximize connect time.',
    ] as $mode => $desc)
                            <div class="flex items-start gap-2">
                                <span
                                    class="font-mono bg-surface-2 px-1.5 py-0.5 rounded text-fg text-xs shrink-0">{{ $mode }}</span>
                                <span class="text-fg-muted text-xs leading-relaxed">{{ $desc }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </form>

</div>
