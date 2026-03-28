<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Activity Logs</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">A full history of system and user activity events.</p>
    </div>

    {{-- ACTIVITY LOGS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Activity Logs</h2>

            <div class="flex flex-wrap items-center gap-2">
                <x-select-dropdown wire-model="filters.type" :value="$filters['type'] ?? ''" placeholder="All Types"
                    :options="[
                        ['value' => 'activity', 'label' => 'Activity'],
                        ['value' => 'audit', 'label' => 'Audit'],
                        ['value' => 'security', 'label' => 'Security'],
                        ['value' => 'system', 'label' => 'System'],
                    ]" />

                <x-select-dropdown wire-model="filters.severity" :value="$filters['severity'] ?? ''" placeholder="All Severities"
                    :options="[
                        ['value' => 'info', 'label' => 'Info'],
                        ['value' => 'warning', 'label' => 'Warning'],
                        ['value' => 'critical', 'label' => 'Critical'],
                    ]" />

                <x-select-dropdown wire-model="filters.source" :value="$filters['source'] ?? ''" placeholder="All Sources"
                    :options="[
                        ['value' => 'web', 'label' => 'Web'],
                        ['value' => 'api', 'label' => 'API'],
                        ['value' => 'job', 'label' => 'Job'],
                        ['value' => 'system', 'label' => 'System'],
                    ]" />
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data x-init="const update = () => {
            const pg = $el.nextElementSibling;
            $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
        };
        update();
        window.addEventListener('resize', update);
        $cleanup(() => window.removeEventListener('resize', update));">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Actor</th>
                        <th class="px-5 py-3 text-left">Action</th>
                        <th class="px-5 py-3 text-left">Subject</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Severity</th>
                        <th class="px-5 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr wire:click="show('{{ $log->id }}')" wire:key="activity-log-{{ $log->id }}"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer">

                            <td class="px-5 py-4 text-fg-3 text-sm">
                                {{ $log->actor ? class_basename($log->actor_type) : 'System' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-fg-2 text-sm">{{ $log->action }}</div>
                                <div class="text-zinc-500 text-xs">{{ $log->event }}</div>
                            </td>

                            <td class="px-5 py-4 text-fg-muted text-sm">
                                {{ $log->subject ? class_basename($log->subject_type) . ' #' . $log->subject_id : '—' }}
                            </td>

                            <td class="px-5 py-4 text-fg-muted text-xs uppercase tracking-wide">
                                {{ $log->type }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide
                                    {{ $log->severity === 'critical'
                                        ? 'bg-red-500/10 text-accent-red'
                                        : ($log->severity === 'warning'
                                            ? 'bg-yellow-500/10 text-accent-yellow'
                                            : 'bg-blue-500/10 text-blue-400') }}">
                                    {{ strtoupper($log->severity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-fg-muted text-xs">
                                {{ $log->performed_at->format('M d, Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-zinc-500 text-center italic">No log entries found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$logs" label="entries" />

    </div>

    {{-- Drawer --}}
    @if ($selectedLog)
        @include('livewire.activitylogs.activity-log-view', ['log' => $selectedLog])
    @endif

</div>
