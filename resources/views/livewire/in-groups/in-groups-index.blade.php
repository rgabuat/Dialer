<div class="space-y-4 p-6 stagger-children">

    <div>
        <h1 class="font-bold text-fg text-xl">In-Groups</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Configure inbound call queues, routing algorithms, and after-hours
            behaviour.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-500/10 px-4 py-3 border border-green-500/20 rounded-lg text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">In-Groups</h2>
            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search in-groups..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                <a href="{{ route('in-group.create') }}" wire:navigate
                    class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    <x-heroicon-o-plus class="w-4 h-4" />
                    New In-Group
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
                        <th class="px-5 py-3 w-full text-left">Name</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Routing</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Agents / DIDs</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Max Wait</th>
                        <th class="px-5 py-3 text-left whitespace-nowrap">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($inGroups as $group)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            {{-- Name + description + campaign --}}
                            <td class="px-5 py-4">
                                <div class="font-semibold text-fg leading-snug">{{ $group->name }}</div>
                                @if ($group->description)
                                    <div class="mt-0.5 text-fg-muted text-xs">{{ $group->description }}</div>
                                @endif
                                @if ($group->campaign)
                                    <div class="mt-1">
                                        <span
                                            class="inline-flex items-center bg-indigo-500/10 px-2 py-0.5 rounded text-indigo-400 text-xs">
                                            {{ $group->campaign->name }}
                                        </span>
                                    </div>
                                @endif
                            </td>

                            {{-- Routing algorithm badge --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @php
                                    $routingLabels = [
                                        'ring_all' => ['Ring All', 'bg-sky-500/10 text-sky-400'],
                                        'round_robin' => ['Round Robin', 'bg-violet-500/10 text-violet-400'],
                                        'fewest_calls' => ['Fewest Calls', 'bg-amber-500/10 text-amber-400'],
                                        'longest_idle' => ['Longest Idle', 'bg-teal-500/10 text-teal-400'],
                                    ];
                                    [$routingLabel, $routingClass] = $routingLabels[$group->agent_routing] ?? [
                                        ucwords(str_replace('_', ' ', $group->agent_routing)),
                                        'bg-surface-2 text-fg-muted',
                                    ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold {{ $routingClass }}">
                                    {{ $routingLabel }}
                                </span>
                                <div class="mt-1 text-fg-muted text-xs">Priority {{ $group->queue_priority }}</div>
                            </td>

                            {{-- Agents / DIDs counts --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center gap-1 text-fg text-sm">
                                        <x-heroicon-o-user-group class="w-3.5 h-3.5 text-fg-muted" />
                                        {{ $group->users_count }}
                                    </span>
                                    <span class="text-surface-3">|</span>
                                    <span class="inline-flex items-center gap-1 text-fg text-sm">
                                        <x-heroicon-o-phone class="w-3.5 h-3.5 text-fg-muted" />
                                        {{ $group->dids_count }}
                                    </span>
                                </div>
                            </td>

                            {{-- Max wait --}}
                            <td class="px-5 py-4 text-fg-muted text-sm whitespace-nowrap">
                                @if ($group->max_wait_seconds)
                                    {{ $group->max_wait_seconds }}s
                                @else
                                    <span class="text-zinc-600 italic">None</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if ($group->is_active)
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

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('in-group.edit', $group) }}" wire:navigate
                                    class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-zinc-500 text-center italic">No in-groups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$inGroups" label="in-groups" />

    </div>

</div>
