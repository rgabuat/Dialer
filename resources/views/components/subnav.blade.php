@php
    $navItems = config('navitems');
    $currentSegment = request()->segment(1);

    // Find the active parent item
    $activeParent = null;
    foreach ($navItems as $item) {
        // Match by direct route
        if (!empty($item['route']) && request()->routeIs($item['route'])) {
            $activeParent = $item;
            break;
        }
        // Match by segments list
        foreach ($item['segments'] ?? [] as $seg) {
            if ($currentSegment === $seg) {
                $activeParent = $item;
                break 2;
            }
        }
        // Match by any child segment/route
        foreach ($item['children'] ?? [] as $child) {
            if (!empty($child['segment']) && $currentSegment === $child['segment']) {
                $activeParent = $item;
                break 2;
            }
            if (!empty($child['route']) && request()->routeIs($child['route'])) {
                $activeParent = $item;
                break 2;
            }
        }
    }

    $children = $activeParent['children'] ?? [];

    // Filter children by permission
    $visibleChildren = array_filter($children, function ($child) {
        $cp = $child['permission'] ?? null;
        return !$cp || (auth()->check() && auth()->user()->can($cp));
    });
@endphp

@if ($activeParent && !empty($visibleChildren))
    <div class="flex items-center gap-0 bg-[#0c0e12] px-6 border-zinc-800 border-b shrink-0">

        {{-- Child tabs --}}
        <nav class="flex items-center gap-0 overflow-x-auto" x-data>
            @foreach ($visibleChildren as $child)
                @php
                    $childUrl = !empty($child['route']) && Route::has($child['route']) ? route($child['route']) : '#';

                    $isChildActive =
                        (!empty($child['segment']) && $currentSegment === $child['segment']) ||
                        (!empty($child['route']) && request()->routeIs($child['route']));
                @endphp
                <a href="{{ $childUrl }}" wire:navigate
                    class="relative px-4 py-3 text-sm font-medium whitespace-nowrap
                           transition-all duration-200 ease-in-out outline-none select-none
                           group
                           {{ $isChildActive ? 'text-white' : 'text-zinc-400 hover:text-white' }}">

                    {{-- Label --}}
                    <span class="z-10 relative">{{ $child['label'] }}</span>

                    {{-- Hover/active background pill --}}
                    <span
                        class="absolute inset-x-1 inset-y-1.5 rounded-md
                                 transition-all duration-200 ease-in-out
                                 {{ $isChildActive ? 'bg-zinc-800/70' : 'bg-zinc-800/0 group-hover:bg-zinc-800/70' }}">
                    </span>

                    {{-- Bottom indicator bar --}}
                    <span
                        class="absolute bottom-0 left-4 right-4 h-[2px] rounded-t
                                 transition-all duration-200 ease-in-out origin-center
                                 {{ $isChildActive
                                     ? 'bg-zinc-500 scale-x-100 opacity-40'
                                     : 'bg-zinc-500 scale-x-0 opacity-0 group-hover:scale-x-100 group-hover:opacity-40' }}">
                    </span>
                </a>
            @endforeach
        </nav>

    </div>
@endif
