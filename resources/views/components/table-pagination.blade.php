@props(['paginator', 'label' => 'records', 'perPageOptions' => [10, 15, 20, 25, 50]])

<div class="flex flex-wrap justify-between items-center gap-3 px-5 py-3 border-zinc-800 border-t" x-data="{
    init() {
            const key = 'perPage:' + window.location.pathname;
            const saved = parseInt(localStorage.getItem(key));
            const valid = @js($perPageOptions);
            if (saved && valid.includes(saved) && saved !== $wire.perPage) {
                $wire.set('perPage', saved);
            }
        },
        savePerPage(val) {
            localStorage.setItem('perPage:' + window.location.pathname, val);
        }
}">
    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-zinc-500 text-xs">Per page</span>
            <select wire:model.live="perPage" x-on:change="savePerPage($event.target.value)"
                class="bg-zinc-800 px-2 py-1 border border-zinc-700 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500 text-zinc-300 text-xs cursor-pointer">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <span class="text-zinc-500 text-xs">
            @if ($paginator->hasPages())
                Showing
                <span class="font-medium text-white">{{ $paginator->firstItem() }} – {{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium text-white">{{ number_format($paginator->total()) }}</span>
            @else
                Showing <span class="font-medium text-white">{{ $paginator->total() }}</span>
            @endif
            {{ $label }}
        </span>
    </div>
    @if ($paginator->hasPages())
        {{ $paginator->links('vendor.pagination.dark') }}
    @endif
</div>
