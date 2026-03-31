<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Workforce</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage weekly rosters, agent shifts, and staffing schedules.</p>
    </div>

    {{-- Flash message --}}
    @if (session('success'))
        <div class="bg-green-500/10 px-4 py-3 border border-green-500/30 rounded-lg text-sm text-accent-green">
            {{ session('success') }}
        </div>
    @endif

    {{-- Rosters table --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-bold text-fg text-base">Rosters</h2>
                <p class="mt-0.5 text-fg-muted text-xs">Select an existing weekly roster to maintain it or create a new
                    one.</p>
            </div>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search rosters..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                <button wire:click="openCreateModal"
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    New
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data="{
            _fn: null,
            init() {
                this._fn = () => {
                    const pg = this.$el.nextElementSibling;
                    this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
                };
                this._fn();
                window.addEventListener('resize', this._fn);
            },
            destroy() { window.removeEventListener('resize', this._fn); }
        }">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Week</th>
                        <th class="px-5 py-3 text-left">Campaign</th>
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Timezone</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rosters as $roster)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            <td class="px-5 py-4 font-semibold text-fg">
                                {{ $roster->week_start->format('D, d M Y') }}
                                <span class="mx-1 font-normal text-fg-muted">–</span>
                                {{ $roster->week_end->format('D, d M Y') }}
                            </td>
                            <td class="px-5 py-4 text-fg-muted">{{ optional($roster->campaign)->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-fg-muted">{{ $roster->label }}</td>
                            <td class="px-5 py-4 font-mono text-fg-muted text-xs">{{ $roster->timezone }}</td>
                            <td class="px-5 py-4">
                                @if ($roster->status === 'published')
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                        <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('workforce.roster.show', $roster) }}" wire:navigate
                                    class="text-fg-muted hover:text-fg text-xs transition">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No rosters found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($rosters->hasPages())
            <div class="px-5 py-3 border-surface border-t">
                {{ $rosters->links() }}
            </div>
        @endif
    </div>

    {{-- ── Create Roster Modal ── --}}
    <x-modal :show="$showCreateModal" wire-close="showCreateModal" title="New Roster" max-width="max-w-lg">

        <div x-data="{
            fmtDate(val) {
                    if (!val) return '';
                    const d = new Date(val + 'T00:00:00');
                    const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                    return days[d.getDay()] + ', ' + d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
                },
                get weekEndLabel() {
                    const val = $wire.createWeekStart;
                    if (!val) return '';
                    const d = new Date(val + 'T00:00:00');
                    d.setDate(d.getDate() + 6);
                    return this.fmtDate(d.toISOString().slice(0, 10));
                }
        }" class="space-y-4">

            {{-- Roster Name --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">
                    Roster Name
                    <span class="font-normal text-fg-muted">(optional)</span>
                </label>
                <x-input wire:model="createName" placeholder="e.g. Week 14" />
            </div>

            {{-- Week Start --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">
                    Week Start
                    <span class="text-red-400">*</span>
                </label>
                <x-date-picker wire-model="createWeekStart" :value="$createWeekStart" placeholder="Pick a start date" />
                @error('createWeekStart')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
            </div>

            {{-- Week End preview --}}
            <div class="space-y-1" x-show="weekEndLabel" x-cloak>
                <label class="font-medium text-fg text-sm">Week End</label>
                <div
                    class="inline-flex items-center gap-2 bg-surface-2 px-3 py-1.5 border border-surface rounded-lg text-fg-muted text-sm cursor-not-allowed select-none">
                    <x-heroicon-o-calendar-days class="w-3.5 h-3.5 text-zinc-500 shrink-0" />
                    <span x-text="weekEndLabel"></span>
                </div>
            </div>

            {{-- Timezone --}}
            <div class="space-y-1.5">
                <label class="font-medium text-fg text-sm">Timezone</label>
                <x-input wire:model="createTimezone" placeholder="e.g. America/Denver" />
                @error('createTimezone')
                    <p class="text-red-400 text-xs">{{ $message }}</p>
                @enderror
                <p class="text-fg-muted text-xs">IANA timezone — e.g. America/Denver, Asia/Manila, Europe/London.</p>
            </div>

        </div>

        <x-slot:footer>
            <button wire:click="$set('showCreateModal', false)"
                class="px-4 py-2 rounded-md text-fg-muted hover:text-fg text-sm transition">
                Cancel
            </button>
            <button wire:click="createRoster" wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <span wire:loading wire:target="createRoster">
                    <x-heroicon-o-arrow-path class="w-4 h-4 animate-spin" />
                </span>
                Create Roster
            </button>
        </x-slot:footer>

    </x-modal>

</div>
