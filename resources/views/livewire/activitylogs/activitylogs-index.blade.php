<div class="p-6">

    {{-- Filters --}}
    <div class="flex gap-3 mb-4">
        <select wire:model.live="filters.type" class="bg-zinc-900 text-white border border-zinc-800 text-sm rounded px-3 py-2">
            <option value="">All Types</option>
            <option value="activity">Activity</option>
            <option value="audit">Audit</option>
            <option value="security">Security</option>
            <option value="system">System</option>
        </select>

        <select wire:model.live="filters.severity" class="bg-zinc-900 text-white border border-zinc-800 text-sm rounded px-3 py-2">
            <option value="">All Severity</option>
            <option value="info">Info</option>
            <option value="warning">Warning</option>
            <option value="critical">Critical</option>
        </select>

        <select wire:model.live="filters.source" class="bg-zinc-900 text-white border border-zinc-800 text-sm rounded px-3 py-2">
            <option value="">All Sources</option>
            <option value="web">Web</option>
            <option value="api">API</option>
            <option value="job">Job</option>
            <option value="system">System</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-lg border border-zinc-800">
        <table class="min-w-full divide-y divide-zinc-800">
            <thead class="bg-zinc-900 text-xs uppercase text-zinc-400">
                <tr>
                    <th class="px-4 py-3">Actor</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Subject</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Severity</th>
                    <th class="px-4 py-3">Date</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-zinc-800 bg-zinc-900">
                @foreach ($logs as $log)
                    <tr wire:click="show('{{ $log->id }}')" wire:key="activity-log-{{ $log->id }}"
                        class="hover:bg-zinc-800/40 transition cursor-pointer">

                        <td class="px-4 py-3 text-sm text-zinc-200">
                            {{ $log->actor ? class_basename($log->actor_type) : 'System' }}
                        </td>

                        <td class="px-4 py-3">
                            <div class="text-sm text-zinc-200">{{ $log->action }}</div>
                            <div class="text-xs text-zinc-500">{{ $log->event }}</div>
                        </td>

                        <td class="px-4 py-3 text-sm text-zinc-300">
                            {{ $log->subject ? class_basename($log->subject_type).' #'.$log->subject_id : '—' }}
                        </td>

                        <td class="px-4 py-3 text-xs text-zinc-400">
                            {{ strtoupper($log->type) }}
                        </td>

                        <td class="px-4 py-3 text-xs">
                            <span class="px-2 py-1 rounded
                                {{ $log->severity === 'critical' ? 'bg-red-500/10 text-red-400' :
                                   ($log->severity === 'warning' ? 'bg-yellow-500/10 text-yellow-400' :
                                    'bg-blue-500/10 text-blue-400') }}">
                                {{ strtoupper($log->severity) }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-xs text-zinc-400">
                            {{ $log->performed_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $logs->links() }}</div>

    {{-- Drawer --}}
    @if($selectedLog)
        @include('livewire.activitylogs.activity-log-view', ['log' => $selectedLog])
    @endif

</div>
