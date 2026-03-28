@props(['paginator', 'label' => 'records', 'perPageOptions' => [10, 15, 20, 25, 50]])


<div class="flex flex-wrap justify-between items-center gap-3 px-5 py-3 border-surface border-t" x-data="{
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
            <span class="text-fg-muted text-xs">Per page</span>
            <select wire:model.live="perPage" x-on:change="savePerPage($event.target.value)"
                class="bg-surface-2 px-2 py-1 border border-surface rounded-md focus:outline-none text-fg-muted text-xs cursor-pointer">
                @foreach ($perPageOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
        </div>
        <span class="text-zinc-500 text-xs">
            @if ($paginator->hasPages())
                Showing
                <span class="font-medium text-fg">{{ $paginator->firstItem() }} – {{ $paginator->lastItem() }}</span>
                of
                <span class="font-medium text-fg">{{ number_format($paginator->total()) }}</span>
            @else
                Showing <span class="font-medium text-fg">{{ $paginator->total() }}</span>
            @endif
            {{ $label }}
        </span>
    </div>
    @if ($paginator->hasPages())
        {{ $paginator->links('vendor.pagination.dark') }}
    @endif
</div>
