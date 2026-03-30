<div>{{-- single Livewire root --}}
    <div class="flex items-center gap-3">

        {{-- STATUS DROPDOWN --}}
        <div class="relative" x-data="{
            open: false,
            statusName: $wire.entangle('currentStatusName'),
            statusColor: $wire.entangle('currentStatusColor'),
        }">
            <button type="button" @click="open = !open"
                class="flex items-center gap-2 bg-surface-2 hover:bg-hover px-3 py-1.5 rounded-lg w-36 text-fg text-sm transition">

                <span class="rounded-full w-2 h-2 shrink-0" :style="`background: ${statusColor}`"></span>

                <span x-text="statusName" class="flex-1 text-left truncate"></span>

                <svg class="opacity-60 w-3.5 h-3.5 text-fg-muted shrink-0" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            <div x-show="open" x-cloak @click.outside="open = false"
                class="left-0 z-50 absolute bg-surface shadow-xl mt-2 border border-surface rounded-lg w-44">

                @foreach ($statuses as $status)
                    <button type="button" wire:click.prevent="setStatus({{ $status->id }})"
                        @click="statusName = '{{ addslashes($status->name) }}'; statusColor = '{{ $status->color }}'; open = false"
                        class="flex items-center gap-2 hover:bg-surface-2 px-3 py-2 w-full text-fg-2 text-sm">

                        <span class="rounded-full w-2 h-2" style="background: {{ $status->color }}"></span>

                        {{ $status->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- LIVE TIMER --}}
        <div wire:ignore x-data="statusTimer(@js($startedAt))" x-init="start()"
            class="bg-surface px-3 py-1 rounded-md font-mono text-fg-muted text-xs">
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
                    this.startInterval()

                    window.addEventListener('agent-status-changed', (e) => {
                        this.startedAt = e.detail.startedAt
                        this.reset()
                    })
                },

                startInterval() {
                    this.tick()
                    this.interval = setInterval(() => this.tick(), 1000)
                },

                reset() {
                    clearInterval(this.interval)
                    this.time = '00:00:00'
                    this.startInterval()
                },

                tick() {
                    if (!this.startedAt) return

                    const start = new Date(this.startedAt).getTime()
                    const now = Date.now()
                    const diff = Math.max(0, Math.floor((now - start) / 1000))

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
</div>
