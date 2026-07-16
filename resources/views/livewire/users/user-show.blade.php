<div class="p-6 space-y-6 stagger-children">

    {{-- Back + Edit header --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('users.index') }}" wire:navigate
            class="inline-flex items-center gap-1.5 text-fg-muted hover:text-fg text-sm transition">
            <x-heroicon-o-arrow-left class="w-4 h-4" />
            Back to Users
        </a>
        <a href="{{ route('user.edit', $user) }}" wire:navigate
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-3 py-1.5 rounded-md text-white text-sm font-medium transition">
            <x-heroicon-o-pencil-square class="w-4 h-4" />
            Edit User
        </a>
    </div>

    {{-- Profile card --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="px-6 py-5 flex items-start gap-5">

            {{-- Avatar --}}
            <div class="relative shrink-0">
                <span
                    class="inline-flex items-center justify-center w-16 h-16 rounded-full text-xl font-bold {{ avatarColor($user->email) }}">
                    {{ strtoupper(substr($user->first_name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                </span>
                @php $status = $user->agentStatus?->statusType; @endphp
                @if ($status)
                    @php $dotColor = $status->color ?? '#6b7280'; @endphp
                    <span class="absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-surface"
                        style="background-color: {{ $dotColor }}"></span>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-fg leading-tight">
                    {{ $user->first_name }} {{ $user->last_name }}
                </h2>
                <p class="text-sm text-fg-muted mt-0.5">{{ $user->email }}</p>

                <div class="flex flex-wrap items-center gap-2 mt-3">
                    {{-- Role --}}
                    @foreach ($user->roles as $role)
                        <span
                            class="inline-flex items-center gap-1 bg-indigo-500/10 text-indigo-400 px-2 py-0.5 rounded-md text-xs font-medium">
                            <x-heroicon-o-shield-check class="w-3 h-3" />
                            {{ $role->name }}
                        </span>
                    @endforeach

                    {{-- User Group --}}
                    @if ($user->userGroup)
                        <span
                            class="inline-flex items-center gap-1 bg-zinc-700/50 text-zinc-300 px-2 py-0.5 rounded-md text-xs font-medium">
                            <x-heroicon-o-user-group class="w-3 h-3" />
                            {{ $user->userGroup->name }}
                        </span>
                    @endif

                    {{-- Agent status --}}
                    @if ($status)
                        @php
                            $color = $status->color ?? '#6b7280';
                            $bgAlpha = $color . '1a';
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs font-medium"
                            style="background-color: {{ $bgAlpha }}; color: {{ $color }}">
                            <span class="w-1.5 h-1.5 rounded-full"
                                style="background-color: {{ $color }}"></span>
                            {{ $status->name }}
                        </span>
                    @else
                        <span
                            class="inline-flex items-center gap-1.5 bg-zinc-700/40 text-zinc-500 px-2 py-0.5 rounded-md text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-zinc-600"></span>
                            Offline
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Meta grid --}}
        <div class="border-t border-surface grid grid-cols-2 sm:grid-cols-4 divide-x divide-surface">
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">ID</p>
                <p class="mt-1 text-sm font-semibold text-fg">{{ $user->id }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Job Title</p>
                <p class="mt-1 text-sm text-fg">{{ $jobTitle ?: '—' }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Mobile</p>
                <p class="mt-1 text-sm text-fg">{{ $mobile ?: '—' }}</p>
            </div>
            <div class="px-5 py-4">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-zinc-500">Joined</p>
                <p class="mt-1 text-sm text-fg">{{ $user->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>

    {{-- Recent conversations --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
        <div class="px-5 py-4 border-b border-surface">
            <h3 class="font-semibold text-fg text-sm">Recent Conversations</h3>
            <p class="text-xs text-zinc-500 mt-0.5">Last {{ $conversations->total() }} call records assigned to this
                agent.</p>
        </div>

        <div class="overflow-auto">
            <table class="min-w-full text-sm">
                <thead class="sticky top-0 z-10 bg-surface">
                    <tr class="border-b border-surface text-zinc-500 text-xs uppercase tracking-wider font-semibold">
                        <th class="px-5 py-3 text-left">Contact</th>
                        <th class="px-5 py-3 text-left">Direction</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Duration</th>
                        <th class="px-5 py-3 text-left">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($conversations as $conv)
                        <tr class="border-b border-surface hover:bg-hover transition">
                            <td class="px-5 py-3 text-fg font-medium">
                                {{ $conv->contact_name ?: $conv->contact_phone ?: '—' }}
                                @if ($conv->contact_name && $conv->contact_phone)
                                    <div class="text-xs text-zinc-500">{{ $conv->contact_phone }}</div>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($conv->direction === 'inbound')
                                    <span class="inline-flex items-center gap-1 text-blue-400 text-xs">
                                        <x-heroicon-o-phone-arrow-down-left class="w-3.5 h-3.5" /> Inbound
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-violet-400 text-xs">
                                        <x-heroicon-o-phone-arrow-up-right class="w-3.5 h-3.5" /> Outbound
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @php
                                    $badge = match ($conv->status) {
                                        'completed' => 'bg-green-500/10 text-green-400',
                                        'in_progress' => 'bg-blue-500/10 text-blue-400',
                                        'abandoned' => 'bg-red-500/10 text-red-400',
                                        default => 'bg-zinc-700/40 text-zinc-400',
                                    };
                                @endphp
                                <span
                                    class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $badge }}">
                                    {{ ucfirst(str_replace('_', ' ', $conv->status)) }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-fg-muted text-xs">
                                {{ $conv->duration_seconds ? gmdate('H:i:s', $conv->duration_seconds) : '—' }}
                            </td>
                            <td class="px-5 py-3 text-fg-muted text-xs">
                                {{ $conv->started_at?->format('M j, Y H:i') ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-zinc-500 italic text-sm">No
                                conversations yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-table-pagination :paginator="$conversations" label="conversations" />
    </div>

</div>
