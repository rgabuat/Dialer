<div class="space-y-6 p-6 max-w-2xl" x-data="{
    fmtDate(val) {
            if (!val) return '';
            const d = new Date(val + 'T00:00:00');
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
        },
        get weekEndLabel() {
            const val = $wire.weekStart;
            if (!val) return '';
            const d = new Date(val + 'T00:00:00');
            d.setDate(d.getDate() + 6);
            return this.fmtDate(d.toISOString().slice(0, 10));
        },
        get weekStartLabel() { return this.fmtDate($wire.weekStart); }
}">

    {{-- Page header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('workforce.rosters.index') }}" wire:navigate
            class="flex justify-center items-center bg-surface hover:bg-hover border border-surface rounded-lg w-8 h-8 text-fg-muted hover:text-fg transition">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
        </a>
        <div>
            <h1 class="font-bold text-fg text-xl">New Roster</h1>
            <p class="text-zinc-500 text-sm">Create or update a roster.</p>
        </div>
    </div>

    {{-- Form card --}}
    <div class="space-y-6 bg-surface p-6 border border-surface rounded-xl">

        <div>
            <h2 class="font-semibold text-fg text-sm">Basics</h2>
            <p class="mt-0.5 text-fg-muted text-xs">Enter the basic information about this roster.</p>
        </div>

        <div class="space-y-4">

            {{-- Roster Name (optional) --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">
                    Roster Name
                    <span class="font-normal text-fg-muted">(optional)</span>
                </label>
                <x-input wire:model="name" placeholder="e.g. Week 14" />
            </div>

            {{-- Week Start --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">
                    Week Start
                    <span class="text-red-400">*</span>
                </label>
                <div>
                    <x-date-picker wire-model="weekStart" :value="$weekStart" placeholder="Pick a start date" />
                </div>
                @error('weekStart')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Week End — auto-calculated, read-only preview --}}
            <div class="space-y-1.5" x-show="weekEndLabel" x-cloak>
                <label class="font-medium text-fg text-sm">Week End</label>
                <div
                    class="inline-flex items-center gap-2 bg-surface-2 px-3 py-1.5 border border-surface rounded-lg text-fg-muted text-sm cursor-not-allowed select-none">
                    <x-heroicon-o-calendar-days class="w-3.5 h-3.5 text-zinc-500 shrink-0" />
                    <span x-text="weekEndLabel"></span>
                </div>
                <p class="text-fg-muted text-xs">Auto-calculated — 6 days after week start (Sunday → Saturday).</p>
            </div>

            {{-- Timezone --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">Timezone</label>
                <x-input wire:model="timezone" placeholder="e.g. America/Denver" />
                @error('timezone')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
                <p class="text-fg-muted text-xs">Use a valid IANA timezone identifier, e.g. America/Denver, Asia/Manila,
                    Europe/London.</p>
            </div>

        </div>

        {{-- Actions --}}
        <div class="flex justify-end items-center gap-3 pt-2">
            <button wire:click="save" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <span wire:loading wire:target="save">
                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                </span>
                Create Roster
            </button>
            <a href="{{ route('workforce.rosters.index') }}" wire:navigate
                class="inline-flex items-center px-4 py-2 rounded-md font-medium text-fg-muted hover:text-fg text-sm transition">
                Cancel
            </a>
        </div>

    </div>

</div>
