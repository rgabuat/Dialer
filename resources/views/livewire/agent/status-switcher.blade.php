<div class="flex items-center gap-3">

    {{-- STATUS DROPDOWN --}}
    <div class="relative" x-data="{ open: false }">
        <button type="button"
            @click="open = !open"
            class="flex items-center gap-2 rounded-lg
                   bg-zinc-800 px-3 py-2 text-sm
                   text-zinc-200 hover:bg-zinc-700">

            <span
                class="h-2 w-2 rounded-full"
                style="background: {{ $currentStatus->color ?? '#6b7280' }}"
            ></span>

            {{ $currentStatus->name ?? 'Other' }}

            <svg class="h-4 w-4 opacity-60" fill="none" stroke="currentColor"
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <div
            x-show="open"
            x-cloak
            @click.outside="open = false"
            class="absolute left-0 mt-2 w-44
                   rounded-lg border border-zinc-800
                   bg-zinc-900 shadow-xl z-50">

            @foreach ($statuses as $status)
                <button type="button"
                    wire:click.prevent="setStatus({{ $status->id }})"
                    @click="open = false"
                    class="flex w-full items-center gap-2
                           px-3 py-2 text-sm text-zinc-200
                           hover:bg-zinc-800">

                    <span
                        class="h-2 w-2 rounded-full"
                        style="background: {{ $status->color }}"
                    ></span>

                    {{ $status->name }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- LIVE TIMER --}}
    <div
        wire:key="agent-status-timer-{{ $startedAt }}"
        x-data="statusTimer('{{ $startedAt }}')"
        x-init="start()"
        class="rounded-md bg-zinc-900 px-3 py-1
               text-xs font-mono text-zinc-400">
        <span x-text="time"></span>
    </div>

</div>

<script>
    function statusTimer(startedAt) {
        return {
            startedAt,
            time: '00:00:00',
            interval: null,

            start() {
                this.tick()
                this.interval = setInterval(() => this.tick(), 1000)
            },

            tick() {
                if (!this.startedAt) return

                const start = new Date(this.startedAt).getTime()
                const now = Date.now()
                const diff = Math.floor((now - start) / 1000)

                const h = String(Math.floor(diff / 3600)).padStart(2, '0')
                const m = String(Math.floor((diff % 3600) / 60)).padStart(2, '0')
                const s = String(diff % 60).padStart(2, '0')

                this.time = `${h}:${m}:${s}`
            },

            destroy() {
                clearInterval(this.interval)
            }
        }
    }
</script>