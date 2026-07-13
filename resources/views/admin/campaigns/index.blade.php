@extends('admin.layouts.app', ['heading' => 'Campaigns'])

@section('content')

{{-- Livewire modal component (handles open/close via dispatched events) --}}
@livewire('admin.campaign-modal')

<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-fg">Campaigns</h2>
            <p class="text-sm text-fg-muted mt-0.5">Manage all call campaigns in the system.</p>
        </div>
        <button type="button"
            onclick="Livewire.dispatch('open-create')"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            New Campaign
        </button>
    </div>

    {{-- Flash --}}
    @if (session('success'))
        <div class="flex items-center gap-2 bg-green-500/10 border border-green-500/20 rounded-lg px-4 py-3 text-green-400 text-sm">
            <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between gap-3 px-5 py-3 border-b border-surface">
            <form method="GET" action="{{ route('admin.campaigns.index') }}" class="flex items-center gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search campaigns…"
                    class="bg-surface-2 border border-surface focus:border-zinc-500 rounded-lg px-3 py-1.5 text-sm text-fg placeholder-fg-muted focus:outline-none w-52 transition">
                <button type="submit"
                    class="px-3 py-1.5 bg-surface-2 border border-surface rounded-lg text-sm text-fg-muted hover:text-fg transition">Search</button>
                @if ($search)
                    <a href="{{ route('admin.campaigns.index') }}" class="text-xs text-fg-muted hover:text-fg transition">Clear</a>
                @endif
            </form>
            <span class="text-xs text-fg-muted">{{ $campaigns->total() }} total</span>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-fg">
                <thead class="bg-surface">
                    <tr class="border-b border-surface text-xs font-semibold text-zinc-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Type</th>
                        <th class="px-5 py-3 text-left">Dial Mode</th>
                        <th class="px-5 py-3 text-left">In-Groups</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Created</th>
                        <th class="px-5 py-3 text-right w-10"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($campaigns as $campaign)
                        @php
                            $typeColors = ['OUTBOUND'=>'bg-blue-500/10 text-blue-400','INBOUND'=>'bg-emerald-500/10 text-emerald-400','BLENDED'=>'bg-fuchsia-500/10 text-fuchsia-400'];
                            $modeColors = ['MANUAL'=>'bg-surface-2 text-fg-muted','PREVIEW'=>'bg-yellow-500/10 text-yellow-400','PROGRESSIVE'=>'bg-indigo-500/10 text-indigo-400','PREDICTIVE'=>'bg-orange-500/10 text-orange-400'];
                        @endphp
                        <tr class="border-b border-surface hover:bg-hover transition">
                            <td class="px-5 py-3">
                                <p class="font-semibold text-fg leading-tight">{{ $campaign->name }}</p>
                                @if ($campaign->description)
                                    <p class="text-xs text-fg-muted mt-0.5 truncate max-w-[200px]">{{ $campaign->description }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $typeColors[$campaign->type] ?? 'bg-surface-2 text-fg-muted' }}">
                                    {{ $campaign->type ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold {{ $modeColors[$campaign->dial_mode] ?? 'bg-surface-2 text-fg-muted' }}">
                                    {{ $campaign->dial_mode ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-xs text-fg-muted">
                                {{ $campaign->inGroups->count() ? $campaign->inGroups->pluck('name')->join(', ') : '—' }}
                            </td>
                            <td class="px-5 py-3">
                                @if ($campaign->is_active)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-fg-muted">
                                        <span class="w-1.5 h-1.5 rounded-full bg-zinc-600 inline-block"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-xs text-fg-muted whitespace-nowrap">{{ $campaign->created_at->format('M d, Y') }}</td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button type="button"
                                        onclick="Livewire.dispatch('open-edit', { id: {{ $campaign->id }} })"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-fg-muted hover:text-fg hover:bg-hover border border-transparent hover:border-surface transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                        Edit
                                    </button>
                                    <form method="POST" action="{{ route('admin.campaigns.destroy', $campaign) }}"
                                        onsubmit="return confirm('Delete \'{{ addslashes($campaign->name) }}\'? Cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-md text-xs font-medium text-fg-muted hover:text-red-400 hover:bg-red-500/10 border border-transparent hover:border-red-500/20 transition">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center text-fg-muted">
                                <p class="text-sm font-medium">No campaigns found</p>
                                @if ($search)
                                    <p class="text-xs mt-1">Try a different search term.</p>
                                @else
                                    <p class="text-xs mt-1">Click <strong>New Campaign</strong> to create the first one.</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($campaigns->hasPages())
            <div class="px-5 py-3 border-t border-surface">
                {{ $campaigns->links() }}
            </div>
        @endif
    </div>
</div>

@endsection
