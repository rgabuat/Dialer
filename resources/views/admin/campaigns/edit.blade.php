@extends('admin.layouts.app', ['heading' => 'Edit Campaign'])

@section('content')
    <div class="max-w-2xl space-y-5">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-fg-muted">
            <a href="{{ route('admin.campaigns.index') }}" class="hover:text-fg transition">Campaigns</a>
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
            <span class="text-fg truncate max-w-[200px]">{{ $campaign->name }}</span>
        </div>

        <form method="POST" action="{{ route('admin.campaigns.update', $campaign) }}" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Basic Info --}}
            <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-fg text-sm border-b border-surface pb-3">Campaign Details</h3>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-fg-muted">Name <span class="text-red-400">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $campaign->name) }}" required
                        class="w-full bg-surface-2 border @error('name') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition">
                    @error('name')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-fg-muted">Description</label>
                    <textarea name="description" rows="3"
                        class="w-full bg-surface-2 border @error('description') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-none"
                        placeholder="Optional description…">{{ old('description', $campaign->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1"
                            {{ old('is_active', $campaign->is_active) ? 'checked' : '' }} class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                    <span class="text-sm text-fg">Active</span>
                </div>
            </div>

            {{-- Dialer Settings --}}
            <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-fg text-sm border-b border-surface pb-3">Dialer Settings</h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Type <span
                                class="text-red-400">*</span></label>
                        <select name="type" required
                            class="w-full bg-surface-2 border @error('type') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            @foreach ($types as $t)
                                <option value="{{ $t }}"
                                    {{ old('type', $campaign->type) === $t ? 'selected' : '' }}>{{ $t }}
                                </option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Dial Mode <span
                                class="text-red-400">*</span></label>
                        <select name="dial_mode" required
                            class="w-full bg-surface-2 border @error('dial_mode') border-red-500 @else border-surface @enderror rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                            @foreach ($dialModes as $m)
                                <option value="{{ $m }}"
                                    {{ old('dial_mode', $campaign->dial_mode) === $m ? 'selected' : '' }}>
                                    {{ $m }}</option>
                            @endforeach
                        </select>
                        @error('dial_mode')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Caller ID</label>
                        <input type="text" name="caller_id" value="{{ old('caller_id', $campaign->caller_id) }}"
                            placeholder="+1xxxxxxxxxx"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition font-mono">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Dial Level</label>
                        <input type="number" name="dial_level" value="{{ old('dial_level', $campaign->dial_level) }}"
                            min="0" step="0.01"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">ACW Seconds</label>
                        <input type="number" name="acw_seconds" value="{{ old('acw_seconds', $campaign->acw_seconds) }}"
                            min="0"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Hopper Level</label>
                        <input type="number" name="hopper_level"
                            value="{{ old('hopper_level', $campaign->hopper_level) }}" min="0"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                    <div class="space-y-1">
                        <label class="block text-xs font-medium text-fg-muted">Max Calls</label>
                        <input type="number" name="max_calls" value="{{ old('max_calls', $campaign->max_calls) }}"
                            min="0"
                            class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                    </div>
                </div>
            </div>

            {{-- Recording --}}
            <div class="bg-surface border border-surface rounded-xl p-5 space-y-4">
                <h3 class="font-semibold text-fg text-sm border-b border-surface pb-3">Recording</h3>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="recording_enabled" value="0">
                        <input type="checkbox" name="recording_enabled" value="1"
                            {{ old('recording_enabled', $campaign->recording_enabled) ? 'checked' : '' }}
                            class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-zinc-700 peer-focus:outline-none rounded-full peer peer-checked:bg-indigo-600 transition after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                        </div>
                    </label>
                    <span class="text-sm text-fg">Enable Recording</span>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-medium text-fg-muted">Recording Channels</label>
                    <select name="recording_channels"
                        class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg focus:outline-none focus:border-zinc-500 transition">
                        @foreach ($recChannels as $ch)
                            <option value="{{ $ch }}"
                                {{ old('recording_channels', $campaign->recording_channels) === $ch ? 'selected' : '' }}>
                                {{ ucfirst($ch) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Script --}}
            <div class="bg-surface border border-surface rounded-xl p-5 space-y-2">
                <h3 class="font-semibold text-fg text-sm border-b border-surface pb-3">Script</h3>
                <textarea name="script" rows="6"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-sm text-fg placeholder-fg-muted focus:outline-none focus:border-zinc-500 transition resize-y"
                    placeholder="Agent script for this campaign…">{{ old('script', $campaign->script) }}</textarea>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.campaigns.index') }}"
                    class="text-sm text-fg-muted hover:text-fg transition">Cancel</a>
            </div>
        </form>
    </div>
@endsection
