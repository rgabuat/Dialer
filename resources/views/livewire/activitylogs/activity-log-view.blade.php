<div class="z-50 fixed inset-0 flex justify-end bg-black/50">

    <div class="bg-surface-4 p-6 border-surface border-l w-full max-w-xl overflow-y-auto">

        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="font-semibold text-fg text-lg">
                    Activity Details
                </h2>
                <div class="text-zinc-500 text-xs">
                    {{ $log->performed_at->format('M d, Y H:i:s') }}
                </div>
            </div>

            <button wire:click="closeDrawer" class="text-zinc-400 hover:text-fg-2">
                ✕
            </button>
        </div>

        {{-- Action --}}
        <div class="mb-6">
            <div class="font-medium text-fg-2 text-sm">
                {{ $log->action }}
            </div>
            <div class="text-zinc-500 text-xs">
                {{ $log->event }}
            </div>
        </div>

        {{-- Primary Changes --}}
        @if (!empty($log->properties))
            <div class="mb-8">
                <h3 class="mb-2 font-semibold text-fg-3 text-sm">
                    Changes (Primary)
                </h3>

                <pre class="bg-surface p-4 border border-surface rounded overflow-x-auto text-fg-2 text-xs">
{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </div>
        @endif

        {{-- Batch Timeline --}}
        @if ($batchLogs->isNotEmpty())
            <div class="mb-8">
                <h3 class="mb-3 font-semibold text-fg-3 text-sm">
                    Batch Timeline
                </h3>

                @foreach ($batchLogs as $item)
                    <div
                        class="border-l border-surface-2 pl-4 py-2
                        {{ $item->id === $log->id ? 'bg-surface-2/60' : '' }}">
                        <div class="text-zinc-400 text-xs">
                            {{ $item->performed_at->format('H:i:s') }}
                        </div>
                        <div class="text-fg-2 text-sm">
                            {{ $item->action }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Batch Changes --}}
        @if ($batchLogs->isNotEmpty())
            <div>
                <h3 class="mb-3 font-semibold text-fg-3 text-sm">
                    Changes (Batch)
                </h3>

                @foreach ($batchLogs as $item)
                    @if (!empty($item->properties))
                        <div class="mb-4 border border-surface rounded">
                            <div class="bg-surface px-3 py-2 text-zinc-400 text-xs">
                                {{ $item->performed_at->format('H:i:s') }} — {{ $item->event }}
                            </div>

                            <pre class="p-3 overflow-x-auto text-fg-2 text-xs">
{{ json_encode($item->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                            </pre>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</div>
