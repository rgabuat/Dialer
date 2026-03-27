<div class="space-y-4 p-6">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Activity Logs</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">A full history of system and user activity events.</p>
    </div>

    {{-- ACTIVITY LOGS TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">Activity Logs</h2>

            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <select wire:model.live="filters.type"
                        class="bg-zinc-800 py-1.5 pr-8 pl-3 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 text-zinc-300 text-sm appearance-none cursor-pointer">
                        <option value="">Type: All</option>
                        <option value="activity">Activity</option>
                        <option value="audit">Audit</option>
                        <option value="security">Security</option>
                        <option value="system">System</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="top-1/2 right-2 absolute w-3.5 h-3.5 text-zinc-500 -translate-y-1/2 pointer-events-none" />
                </div>

                <div class="relative">
                    <select wire:model.live="filters.severity"
                        class="bg-zinc-800 py-1.5 pr-8 pl-3 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 text-zinc-300 text-sm appearance-none cursor-pointer">
                        <option value="">Severity: All</option>
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="top-1/2 right-2 absolute w-3.5 h-3.5 text-zinc-500 -translate-y-1/2 pointer-events-none" />
                </div>

                <div class="relative">
                    <select wire:model.live="filters.source"
                        class="bg-zinc-800 py-1.5 pr-8 pl-3 border border-zinc-700 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500/40 text-zinc-300 text-sm appearance-none cursor-pointer">
                        <option value="">Source: All</option>
                        <option value="web">Web</option>
                        <option value="api">API</option>
                        <option value="job">Job</option>
                        <option value="system">System</option>
                    </select>
                    <x-heroicon-o-chevron-down
                        class="top-1/2 right-2 absolute w-3.5 h-3.5 text-zinc-500 -translate-y-1/2 pointer-events-none" />
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
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
                            class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition cursor-pointer">

                            <td class="px-5 py-4 text-zinc-300 text-sm">
                                {{ $log->actor ? class_basename($log->actor_type) : 'System' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="text-zinc-200 text-sm">{{ $log->action }}</div>
                                <div class="text-zinc-500 text-xs">{{ $log->event }}</div>
                            </td>

                            <td class="px-5 py-4 text-zinc-400 text-sm">
                                {{ $log->subject ? class_basename($log->subject_type) . ' #' . $log->subject_id : '—' }}
                            </td>

                            <td class="px-5 py-4 text-zinc-400 text-xs uppercase tracking-wide">
                                {{ $log->type }}
                            </td>

                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide
                                    {{ $log->severity === 'critical'
                                        ? 'bg-red-500/10 text-red-400'
                                        : ($log->severity === 'warning'
                                            ? 'bg-yellow-500/10 text-yellow-400'
                                            : 'bg-blue-500/10 text-blue-400') }}">
                                    {{ strtoupper($log->severity) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-zinc-400 text-xs">
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
        @if ($logs->hasPages())
            <div class="flex justify-between items-center px-5 py-3 border-zinc-800 border-t">
                <span class="text-zinc-500 text-xs">
                    Showing
                    <span class="font-medium text-white">{{ $logs->firstItem() }} – {{ $logs->lastItem() }}</span>
                    of
                    <span class="font-medium text-white">{{ number_format($logs->total()) }}</span>
                    entries
                </span>
                {{ $logs->links() }}
            </div>
        @endif

    </div>

    {{-- Drawer --}}
    @if ($selectedLog)
        @include('livewire.activitylogs.activity-log-view', ['log' => $selectedLog])
    @endif

</div>
