<div class="fixed inset-0 z-50 flex justify-end bg-black/50">

    <div class="w-full max-w-xl bg-zinc-950 border-l border-zinc-800 p-6 overflow-y-auto">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-zinc-100">
                    Activity Details
                </h2>
                <div class="text-xs text-zinc-500">
                    {{ $log->performed_at->format('M d, Y H:i:s') }}
                </div>
            </div>

            <button wire:click="closeDrawer" class="text-zinc-400 hover:text-zinc-200">
                ✕
            </button>
        </div>

        {{-- Action --}}
        <div class="mb-6">
            <div class="text-sm font-medium text-zinc-200">
                {{ $log->action }}
            </div>
            <div class="text-xs text-zinc-500">
                {{ $log->event }}
            </div>
        </div>

        {{-- Primary Changes --}}
        @if(!empty($log->properties))
            <div class="mb-8">
                <h3 class="text-sm font-semibold text-zinc-300 mb-2">
                    Changes (Primary)
                </h3>

                <pre class="bg-zinc-900 border border-zinc-800 rounded p-4 text-xs text-zinc-200 overflow-x-auto">
{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </div>
        @endif

        {{-- Batch Timeline --}}
        @if($batchLogs->isNotEmpty())
            <div class="mb-8">
                <h3 class="text-sm font-semibold text-zinc-300 mb-3">
                    Batch Timeline
                </h3>

                @foreach($batchLogs as $item)
                    <div class="border-l border-zinc-700 pl-4 py-2
                        {{ $item->id === $log->id ? 'bg-zinc-800/40' : '' }}">
                        <div class="text-xs text-zinc-400">
                            {{ $item->performed_at->format('H:i:s') }}
                        </div>
                        <div class="text-sm text-zinc-200">
                            {{ $item->action }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Batch Changes --}}
        @if($batchLogs->isNotEmpty())
            <div>
                <h3 class="text-sm font-semibold text-zinc-300 mb-3">
                    Changes (Batch)
                </h3>

                @foreach($batchLogs as $item)
                    @if(!empty($item->properties))
                        <div class="mb-4 border border-zinc-800 rounded">
                            <div class="px-3 py-2 bg-zinc-900 text-xs text-zinc-400">
                                {{ $item->performed_at->format('H:i:s') }} — {{ $item->event }}
                            </div>

                            <pre class="p-3 text-xs text-zinc-200 overflow-x-auto">
{{ json_encode($item->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                            </pre>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif

    </div>
</div>
