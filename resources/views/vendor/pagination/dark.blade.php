@if ($paginator->hasPages())
    <nav class="flex items-center gap-1" aria-label="Pagination">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span
                class="inline-flex justify-center items-center rounded-md w-8 h-8 text-fg-muted cursor-not-allowed select-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </span>
        @else
            <button wire:click="previousPage" wire:loading.attr="disabled"
                class="inline-flex justify-center items-center hover:bg-surface-2 rounded-md w-8 h-8 text-fg-muted hover:text-fg transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        @endif

        {{-- Pages --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="inline-flex justify-center items-center w-8 h-8 text-fg-muted text-sm select-none">…</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                            class="inline-flex justify-center items-center bg-blue-600 rounded-md w-8 h-8 font-medium text-white text-sm select-none">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})"
                            class="inline-flex justify-center items-center hover:bg-surface-2 rounded-md w-8 h-8 text-fg-muted hover:text-fg text-sm transition">
                            {{ $page }}
                        </button>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <button wire:click="nextPage" wire:loading.attr="disabled"
                class="inline-flex justify-center items-center hover:bg-surface-2 rounded-md w-8 h-8 text-fg-muted hover:text-fg transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        @else
            <span
                class="inline-flex justify-center items-center rounded-md w-8 h-8 text-fg-muted cursor-not-allowed select-none">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </span>
        @endif

    </nav>
@endif
