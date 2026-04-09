@php
    $categories = [
        'SALE' => 'Sale',
        'DNC' => 'Do Not Call',
        'CALLBACK' => 'Callback',
        'RETRY' => 'Retry',
        'OTHER' => 'Other',
    ];
@endphp

<div class="max-w-xl mx-auto space-y-6">
    {{-- Header --}}
    <div>
        <nav class="flex items-center gap-1.5 text-fg-muted text-xs mb-1">
            <a href="{{ route('campaigns.index') }}" wire:navigate class="hover:text-fg transition">Campaigns</a>
            <span>/</span>
            <a href="{{ route('campaign.dispositions', $campaign) }}" wire:navigate
                class="hover:text-fg transition">Dispositions</a>
            <span>/</span>
            <span class="text-fg">{{ $disposition->name }}</span>
        </nav>
        <h1 class="font-bold text-fg text-xl">Edit Disposition</h1>
    </div>

    <form wire:submit="save" class="bg-surface border border-surface rounded-xl divide-y divide-surface">
        {{-- Name & Code --}}
        <div class="grid grid-cols-2 gap-4 p-5">
            <div>
                <label class="block mb-1 font-semibold text-fg text-xs">Name <span
                        class="text-accent-red">*</span></label>
                <input wire:model="name" type="text"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                @error('name')
                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="block mb-1 font-semibold text-fg text-xs">Code <span
                        class="text-accent-red">*</span></label>
                <input wire:model="code" type="text"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
                @error('code')
                    <p class="text-accent-red text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Category --}}
        <div class="p-5">
            <label class="block mb-1.5 font-semibold text-fg text-xs">Category</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="category" value="{{ $value }}" class="sr-only peer">
                        <span
                            class="inline-flex items-center px-3 py-1.5 rounded-lg border border-surface text-fg-muted text-xs font-semibold
                            peer-checked:border-fuchsia-500 peer-checked:text-fuchsia-400 peer-checked:bg-fuchsia-500/10 transition">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Flags --}}
        <div class="grid grid-cols-2 gap-4 p-5">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="is_dnc"
                    class="w-4 h-4 rounded text-fuchsia-600 focus:ring-fuchsia-500">
                <span class="text-fg text-sm">Mark as DNC</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="requires_callback"
                    class="w-4 h-4 rounded text-fuchsia-600 focus:ring-fuchsia-500">
                <span class="text-fg text-sm">Requires Callback</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="is_active"
                    class="w-4 h-4 rounded text-fuchsia-600 focus:ring-fuchsia-500">
                <span class="text-fg text-sm">Active</span>
            </label>
            <div>
                <label class="block mb-1 font-semibold text-fg text-xs">Sort Order</label>
                <input wire:model="sort_order" type="number" min="0"
                    class="w-full bg-surface-2 border border-surface rounded-lg px-3 py-2 text-fg text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-500">
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-2 px-5 py-4 bg-surface-2">
            <a href="{{ route('campaign.dispositions', $campaign) }}" wire:navigate
                class="inline-flex items-center px-4 py-2 rounded-lg border border-surface text-fg-muted hover:text-fg text-sm transition">
                Cancel
            </a>
            <button type="submit"
                class="inline-flex items-center gap-1.5 bg-fuchsia-600 hover:bg-fuchsia-500 px-4 py-2 rounded-lg font-semibold text-white text-sm transition">
                <span wire:loading.remove wire:target="save">Save Changes</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </div>
    </form>
</div>
