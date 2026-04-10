<div class="space-y-4 p-6 stagger-children">

    <div>
        <h1 class="font-bold text-fg text-xl">DIDs</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Direct inward dial numbers mapped to in-groups or IVR menus.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 px-4 py-3 border border-green-500/20 rounded-lg text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">DIDs</h2>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search number or description..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-52 text-fg text-sm transition placeholder-fg-muted">
                <a href="{{ route('did.create') }}" wire:navigate
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    New DID
                </a>
            </div>
        </div>

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
                        <th class="px-5 py-3 text-left">Phone Number</th>
                        <th class="px-5 py-3 text-left">Friendly Name</th>
                        <th class="px-5 py-3 text-left">Routes To</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dids as $did)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            <td class="px-5 py-4 font-mono font-semibold text-fg">{{ $did->phone_number }}</td>
                            <td class="px-5 py-4 text-fg-muted">{{ $did->cidNumber?->friendly_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">
                                @if ($did->inGroup)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-blue-500/10 px-2 py-0.5 rounded text-blue-400 text-xs">
                                        In-Group: {{ $did->inGroup->name }}
                                    </span>
                                @elseif ($did->ivrMenu)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-purple-500/10 px-2 py-0.5 rounded text-purple-400 text-xs">
                                        IVR: {{ $did->ivrMenu->name }}
                                    </span>
                                @else
                                    <span class="text-zinc-500 italic">Not configured</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($did->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-surface-2 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                        <span class="bg-surface-3 rounded-full w-1.5 h-1.5"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('did.edit', $did) }}" wire:navigate
                                    class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No DIDs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$dids" label="DIDs" />

    </div>

</div>
