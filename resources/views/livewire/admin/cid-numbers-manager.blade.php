<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-fg">CID Numbers</h2>
            <p class="text-sm text-fg-muted mt-0.5">Manage Twilio phone numbers and assign them to CID groups.</p>
        </div>
        <button wire:click="refresh" wire:loading.attr="disabled" type="button"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-surface bg-surface-2 hover:bg-hover text-sm text-fg-muted hover:text-fg transition">
            <svg wire:loading.remove wire:target="refresh" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
            <svg wire:loading wire:target="refresh" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Refresh from Twilio
        </button>
    </div>

    {{-- Flash messages --}}
    @if ($error)
        <div
            class="flex items-start gap-2 bg-red-500/10 border border-red-500/20 rounded-lg px-4 py-3 text-red-400 text-sm">
            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
            </svg>
            {{ $error }}
        </div>
    @endif
    @if ($success)
        <div
            class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-green-400 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ $success }}
        </div>
    @endif

    {{-- Two-column layout --}}
    <div class="grid grid-cols-2 gap-5 items-start">

        {{-- ── LEFT: Twilio numbers ─────────────────────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-surface flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-fg text-sm">Twilio Account Numbers</h3>
                    <p class="text-[11px] text-fg-muted mt-0.5">{{ count($twilioNumbers) }}
                        number{{ count($twilioNumbers) !== 1 ? 's' : '' }} on your Twilio account</p>
                </div>
                <span class="text-[11px] text-fg-muted font-mono">{{ $webhookBase }}</span>
            </div>

            @if (empty($twilioNumbers))
                <div class="px-5 py-12 text-center text-fg-muted text-sm">
                    @if ($error)
                        Could not load Twilio numbers.
                    @else
                        No numbers found on this Twilio account.
                    @endif
                </div>
            @else
                <div class="divide-y divide-surface">
                    @foreach ($twilioNumbers as $num)
                        @php
                            $imported = isset($importedMap[$num['phone_number']]);
                            $pending = $pendingRows[$num['phone_number']] ?? false;
                            $ourWebhook = str_starts_with($num['voice_url'], $webhookBase);
                        @endphp
                        <div class="flex items-start justify-between gap-4 px-5 py-3.5">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="text-sm font-semibold text-fg font-mono">{{ $num['phone_number'] }}</p>
                                    @if ($imported)
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 font-medium">Imported</span>
                                    @endif
                                    @if ($ourWebhook)
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-indigo-500/10 text-indigo-400 font-medium">Webhook
                                            ✓</span>
                                    @else
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-400 font-medium">Webhook
                                            mismatch</span>
                                    @endif
                                </div>
                                <p class="text-xs text-fg-muted mt-0.5">{{ $num['friendly_name'] }}</p>
                                @if (!$ourWebhook && $num['voice_url'])
                                    <p class="text-[10px] text-fg-muted mt-0.5 truncate max-w-[280px] font-mono">
                                        {{ $num['voice_url'] }}</p>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 shrink-0">
                                @if (!$imported)
                                    <button type="button"
                                        wire:click="import('{{ $num['sid'] }}', '{{ $num['phone_number'] }}', '{{ addslashes($num['friendly_name']) }}')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-indigo-500/40 text-indigo-400 hover:bg-indigo-500/10 text-xs font-medium transition {{ $pending ? 'opacity-60' : '' }}">
                                        @if ($pending)
                                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                                    stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z">
                                                </path>
                                            </svg>
                                        @else
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>
                                        @endif
                                        Import
                                    </button>
                                @else
                                    <button type="button"
                                        wire:click="syncWebhook('{{ $num['sid'] }}', '{{ $num['phone_number'] }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg border border-surface bg-surface-2 hover:bg-hover text-xs text-fg-muted hover:text-fg transition {{ $pending ? 'opacity-60' : '' }}">
                                        <svg class="w-3 h-3 {{ $pending ? 'animate-spin' : '' }}" fill="none"
                                            viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                        </svg>
                                        Sync Webhook
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ── RIGHT: Imported / local CID numbers ─────────────────────── --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-surface">
                <h3 class="font-semibold text-fg text-sm">Imported CID Numbers</h3>
                <p class="text-[11px] text-fg-muted mt-0.5">{{ $importedCids->count() }}
                    number{{ $importedCids->count() !== 1 ? 's' : '' }} in local pool</p>
            </div>

            @if ($importedCids->isEmpty())
                <div class="px-5 py-12 text-center text-fg-muted text-sm">
                    No numbers imported yet. Import from the Twilio list.
                </div>
            @else
                <div class="divide-y divide-surface">
                    @foreach ($importedCids as $cid)
                        <div class="px-5 py-3.5 space-y-2">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-fg font-mono">{{ $cid->phone_number }}</p>
                                    @if ($cid->friendly_name)
                                        <p class="text-xs text-fg-muted mt-0.5">{{ $cid->friendly_name }}</p>
                                    @endif
                                    @if ($cid->cidGroup)
                                        <p class="text-[11px] text-indigo-400 mt-1">Group: {{ $cid->cidGroup->name }}
                                        </p>
                                    @else
                                        <p class="text-[11px] text-fg-muted mt-1">No group assigned</p>
                                    @endif
                                </div>
                                <button type="button" wire:click="remove({{ $cid->id }})"
                                    onclick="return confirm('Remove {{ addslashes($cid->phone_number) }} from local pool?')"
                                    class="p-1.5 rounded-lg text-fg-muted hover:text-red-400 hover:bg-red-500/10 transition shrink-0">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                {{-- Active toggle --}}
                                <button type="button" wire:click="toggleActive({{ $cid->id }})"
                                    @class([
                                        'inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium transition',
                                        'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' =>
                                            $cid->is_active,
                                        'bg-surface-2 text-fg-muted hover:bg-hover' => !$cid->is_active,
                                    ])>
                                    <span
                                        class="w-1.5 h-1.5 rounded-full inline-block {{ $cid->is_active ? 'bg-emerald-400' : 'bg-zinc-600' }}"></span>
                                    {{ $cid->is_active ? 'Active' : 'Inactive' }}
                                </button>

                                {{-- Rotation toggle --}}
                                <button type="button" wire:click="toggleRotation({{ $cid->id }})"
                                    @class([
                                        'inline-flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-medium transition',
                                        'bg-cyan-500/10 text-cyan-400 hover:bg-cyan-500/20' => $cid->in_rotation,
                                        'bg-surface-2 text-fg-muted hover:bg-hover' => !$cid->in_rotation,
                                    ])>
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    {{ $cid->in_rotation ? 'In Rotation' : 'Not in Rotation' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
