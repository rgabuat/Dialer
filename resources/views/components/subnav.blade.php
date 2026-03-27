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
        <nav class="flex items-center gap-0 overflow-x-auto">
            @foreach ($visibleChildren as $child)
                @php
                    $childUrl = !empty($child['route']) && Route::has($child['route']) ? route($child['route']) : '#';

                    $isChildActive =
                        (!empty($child['segment']) && $currentSegment === $child['segment']) ||
                        (!empty($child['route']) && request()->routeIs($child['route']));
                @endphp
                <a href="{{ $childUrl }}" wire:navigate
                    class="px-3 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                      {{ $isChildActive
                          ? 'border-white text-white'
                          : 'border-transparent text-zinc-400 hover:text-zinc-200 hover:border-zinc-600' }}">
                    {{ $child['label'] }}
                </a>
            @endforeach
        </nav>

    </div>
@endif
