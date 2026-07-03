<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="font-bold text-fg text-xl">CID Numbers</h1>
            <p class="mt-0.5 text-zinc-500 text-sm">Import Twilio numbers into your CID pool, then organise them into
                <a href="{{ route('cid-groups.index') }}" wire:navigate class="text-fuchsia-400 hover:underline font-medium">CID Groups</a>
                and bind each group to a campaign for exclusive per-campaign rotation.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('cid-groups.index') }}" wire:navigate
                class="inline-flex items-center gap-2 bg-fuchsia-600 hover:bg-fuchsia-500 px-3 py-2 rounded-lg text-white text-sm font-medium transition">
                <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                CID Groups
            </a>
            <button wire:click="refresh" type="button"
                class="inline-flex items-center gap-2 bg-surface-2 hover:bg-surface border border-surface px-3 py-2 rounded-lg text-fg text-sm font-medium transition">
                <x-heroicon-o-arrow-path class="w-4 h-4 text-fg-muted" wire:loading.class="animate-spin"
                    wire:target="refresh" />
                Refresh
            </button>
        </div>
    </div>

    {{-- Error --}}
    @if ($errorMessage)
        <div
            class="flex items-center gap-2 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 text-accent-red text-sm">
            <x-heroicon-o-exclamation-circle class="w-4 h-4 shrink-0" />
            {{ $errorMessage }}
        </div>
    @endif

    {{-- Success --}}
    @if ($successMessage)
        <div
            class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-accent-green text-sm">
            <x-heroicon-o-check-circle class="w-4 h-4 shrink-0" />
            {{ $successMessage }}
        </div>
    @endif

    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-bold text-fg text-base">Imported CID Numbers</h2>
                <p class="text-fg-muted text-xs mt-0.5">Assign numbers to a <a href="{{ route('cid-groups.index') }}" wire:navigate class="text-fuchsia-400 hover:underline">CID Group</a>, then toggle Active and In Rotation per number within that group.</p>
            </div>
        </div>

        @if ($importedCids->isEmpty())
            <p class="px-5 py-6 text-fg-muted text-sm italic">No numbers imported yet. Import from the Twilio table
                below.</p>
        @else
            <div class="overflow-auto">
                <table class="min-w-full text-fg text-sm">
                    <thead class="top-0 z-10 sticky bg-surface">
                        <tr
                            class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                            <th class="px-5 py-3 text-left">Phone Number</th>
                            <th class="px-5 py-3 text-left">Friendly Name</th>
                            <th class="px-5 py-3 text-left">CID Group</th>
                            <th class="px-5 py-3 text-center">Active</th>
                            <th class="px-5 py-3 text-center" title="Participates in the group's round-robin rotation">In Rotation</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($importedCids as $cid)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="px-5 py-3 font-mono font-semibold text-fg">{{ $cid->phone_number }}</td>
                                <td class="px-5 py-3 text-fg-muted text-sm">{{ $cid->friendly_name ?? '—' }}</td>

                                {{-- CID Group --}}
                                <td class="px-5 py-3">
                                    @if ($cid->cidGroup)
                                        <a href="{{ route('cid-group.edit', $cid->cidGroup) }}" wire:navigate
                                            class="inline-flex items-center gap-1.5 bg-fuchsia-500/10 px-2.5 py-1 rounded text-fuchsia-400 text-xs font-medium hover:bg-fuchsia-500/20 transition">
                                            <x-heroicon-s-rectangle-stack class="w-3 h-3" />
                                            {{ $cid->cidGroup->name }}
                                        </a>
                                    @else
                                        <span class="text-fg-muted/50 text-xs italic">Unassigned</span>
                                    @endif
                                </td>

                                {{-- Active toggle --}}
                                <td class="px-5 py-3 text-center">
                                    <button wire:click="toggleActive({{ $cid->id }})" type="button"
                                        class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none {{ $cid->is_active ? 'bg-emerald-500' : 'bg-zinc-600' }}">
                                        <span
                                            class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform {{ $cid->is_active ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                    </button>
                                </td>

                                {{-- In Rotation toggle --}}
                                <td class="px-5 py-3 text-center">
                                    @if ($cid->cidGroup)
                                        <button wire:click="toggleRotation({{ $cid->id }})" type="button"
                                            title="{{ $cid->in_rotation ? 'Remove from' : 'Include in' }} {{ $cid->cidGroup->name }} rotation"
                                            class="relative inline-flex h-5 w-9 items-center rounded-full transition-colors focus:outline-none {{ $cid->in_rotation ? 'bg-blue-500' : 'bg-zinc-600' }}">
                                            <span
                                                class="inline-block h-3.5 w-3.5 transform rounded-full bg-white shadow transition-transform {{ $cid->in_rotation ? 'translate-x-4' : 'translate-x-1' }}"></span>
                                        </button>
                                    @else
                                        <span title="Assign to a CID group first to enable rotation"
                                            class="inline-flex items-center justify-center w-9 h-5">
                                            <span class="w-3 h-3 rounded-full bg-zinc-700 border border-zinc-600" ></span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div
                class="px-5 py-3 border-surface border-t bg-surface-2 flex flex-wrap items-center gap-4 text-xs text-fg-muted">
                <span class="flex items-center gap-1.5"><span
                        class="inline-block w-4 h-2.5 rounded-full bg-emerald-500"></span> Active — number is available
                    for use</span>
                <span class="flex items-center gap-1.5"><span
                        class="inline-block w-4 h-2.5 rounded-full bg-blue-500"></span> In Rotation — participates in
                    the CID group's round-robin pool</span>
                <span class="flex items-center gap-1.5"><span
                        class="inline-block w-3 h-3 rounded-full bg-zinc-700 border border-zinc-600"></span> No group — assign to a
                    <a href="{{ route('cid-groups.index') }}" wire:navigate class="text-fuchsia-400 hover:underline">CID group</a> to enable rotation</span>
            </div>
        @endif

    </div>

    {{-- ── Twilio Phone Numbers ── --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-bold text-fg text-base">Twilio Phone Numbers</h2>
                <p class="text-fg-muted text-xs mt-0.5">{{ count($numbers) }}
                    number{{ count($numbers) !== 1 ? 's' : '' }} on account — import to add to CID pool</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cid-groups.index') }}" wire:navigate
                    class="inline-flex items-center gap-1.5 text-fuchsia-400 hover:text-fuchsia-300 text-sm font-medium transition">
                    <x-heroicon-o-rectangle-stack class="w-4 h-4" />
                    Manage CID Groups
                </a>
                <a href="{{ route('dids.index') }}" wire:navigate
                    class="inline-flex items-center gap-2 text-fg-muted hover:text-fg text-sm transition">
                    <x-heroicon-o-list-bullet class="w-4 h-4" />
                    View DID List
                </a>
            </div>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full text-fg text-sm">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Phone Number</th>
                        <th class="px-5 py-3 text-left">Friendly Name</th>
                        <th class="px-5 py-3 text-left">Capabilities</th>
                        <th class="px-5 py-3 text-left">Webhook</th>
                        <th class="px-5 py-3 text-left">CID Status</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($numbers as $num)
                        @php
                            $phone = $num['phone_number'];
                            $sid = $num['sid'];
                            $caps = $num['capabilities'];
                            $voiceUrl = $num['voice_url'];
                            $imported = isset($importedMap[$phone]);
                            $webhookOk = str_contains($voiceUrl, '/api/call-routing');
                            $pending = $pendingRows[$phone] ?? false;
                        @endphp
                        <tr class="hover:bg-hover border-surface border-b transition">

                            {{-- Phone Number --}}
                            <td class="px-5 py-4 font-mono font-semibold text-fg">{{ $phone }}</td>

                            {{-- Friendly Name --}}
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $num['friendly_name'] }}</td>

                            {{-- Capabilities --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    @if (!empty($caps['voice']))
                                        <span
                                            class="inline-flex items-center gap-1 bg-blue-500/10 px-2 py-0.5 rounded text-blue-400 text-xs font-medium">
                                            <x-heroicon-s-phone class="w-3 h-3" /> Voice
                                        </span>
                                    @endif
                                    @if (!empty($caps['sms']))
                                        <span
                                            class="inline-flex items-center gap-1 bg-emerald-500/10 px-2 py-0.5 rounded text-emerald-400 text-xs font-medium">
                                            <x-heroicon-s-chat-bubble-left class="w-3 h-3" /> SMS
                                        </span>
                                    @endif
                                    @if (!empty($caps['mms']))
                                        <span
                                            class="inline-flex items-center gap-1 bg-violet-500/10 px-2 py-0.5 rounded text-violet-400 text-xs font-medium">
                                            MMS
                                        </span>
                                    @endif
                                </div>
                            </td>

                            {{-- Webhook status --}}
                            <td class="px-5 py-4">
                                @if ($webhookOk)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2 py-0.5 rounded text-accent-green text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Synced
                                    </span>
                                @elseif (empty($voiceUrl))
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2 py-0.5 rounded text-fg-muted text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Not set
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-yellow-500/10 px-2 py-0.5 rounded text-yellow-400 text-xs font-medium"
                                        title="{{ $voiceUrl }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-400"></span> Other
                                    </span>
                                @endif
                            </td>

                            {{-- CID Status --}}
                            <td class="px-5 py-4">
                                @if ($imported)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-blue-500/10 px-2.5 py-1 rounded text-blue-400 text-xs font-medium">
                                        <x-heroicon-s-check-circle class="w-3.5 h-3.5" />
                                        Imported
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded text-fg-muted text-xs font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-500"></span> Not imported
                                    </span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex items-center gap-2 justify-end">
                                    @if (!$imported)
                                        <button
                                            wire:click="import('{{ $sid }}', '{{ $phone }}', '{{ addslashes($num['friendly_name']) }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="import('{{ $sid }}', '{{ $phone }}', '{{ addslashes($num['friendly_name']) }}')"
                                            type="button"
                                            class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 px-3 py-1.5 rounded-md text-white text-xs font-medium transition">
                                            <span wire:loading
                                                wire:target="import('{{ $sid }}', '{{ $phone }}', '{{ addslashes($num['friendly_name']) }}')">
                                                <x-heroicon-o-arrow-path class="w-3 h-3 animate-spin" />
                                            </span>
                                            <span wire:loading.remove
                                                wire:target="import('{{ $sid }}', '{{ $phone }}', '{{ addslashes($num['friendly_name']) }}')">
                                                <x-heroicon-o-arrow-down-tray class="w-3 h-3" />
                                            </span>
                                            Import as CID
                                        </button>
                                    @else
                                        @if (!$webhookOk)
                                            <button
                                                wire:click="syncWebhook('{{ $sid }}', '{{ $phone }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="syncWebhook('{{ $sid }}', '{{ $phone }}')"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 bg-yellow-600/80 hover:bg-yellow-600 disabled:opacity-50 px-3 py-1.5 rounded-md text-white text-xs font-medium transition">
                                                <span wire:loading
                                                    wire:target="syncWebhook('{{ $sid }}', '{{ $phone }}')">
                                                    <x-heroicon-o-arrow-path class="w-3 h-3 animate-spin" />
                                                </span>
                                                <span wire:loading.remove
                                                    wire:target="syncWebhook('{{ $sid }}', '{{ $phone }}')">
                                                    <x-heroicon-o-arrow-path class="w-3 h-3" />
                                                </span>
                                                Sync Webhook
                                            </button>
                                        @else
                                            <button
                                                wire:click="syncWebhook('{{ $sid }}', '{{ $phone }}')"
                                                wire:loading.attr="disabled"
                                                wire:target="syncWebhook('{{ $sid }}', '{{ $phone }}')"
                                                type="button"
                                                class="inline-flex items-center gap-1.5 bg-surface-2 hover:bg-surface border border-surface disabled:opacity-50 px-3 py-1.5 rounded-md text-fg-muted text-xs font-medium transition">
                                                <x-heroicon-o-arrow-path class="w-3 h-3" />
                                                Re-sync
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-zinc-500 text-center italic">
                                @if ($errorMessage)
                                    Twilio connection error — check credentials in .env
                                @else
                                    No phone numbers found on this Twilio account.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Legend --}}
        <div
            class="px-5 py-3 border-surface border-t bg-surface-2 flex flex-wrap items-center gap-4 text-xs text-fg-muted">
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-green-400"></span> Webhook
                points to this app's call router</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-yellow-400"></span> Webhook
                set
                to a different URL</span>
            <span class="flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-zinc-500"></span> No webhook
                configured</span>
        </div>

    </div>

</div>
