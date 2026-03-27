@php
    $allItems = config('navitems');
    $mainItems = array_filter($allItems, fn($i) => empty($i['bottom']));
    $bottomItems = array_filter($allItems, fn($i) => !empty($i['bottom']));

    /**
     * Determine if a nav item (parent) is active based on:
     *  – direct route match
     *  – any segment in its `segments` array matches URL segment(1)
     *  – any child segment or route matches
     */
    $isItemActive = function (array $item) {
        if (!empty($item['route']) && request()->routeIs($item['route'])) {
            return true;
        }
        foreach ($item['segments'] ?? [] as $seg) {
            if (request()->segment(1) === $seg) {
                return true;
            }
        }
        foreach ($item['children'] ?? [] as $child) {
            if (!empty($child['segment']) && request()->segment(1) === $child['segment']) {
                return true;
            }
            if (!empty($child['route']) && request()->routeIs($child['route'])) {
                return true;
            }
        }
        return false;
    };

    /**
     * Resolve the href for a nav item.
     * Parents with children → first accessible child route.
     * Otherwise → direct route or '#'.
     */
    $resolveUrl = function (array $item) {
        $children = $item['children'] ?? [];
        if (!empty($children)) {
            foreach ($children as $child) {
                $cp = $child['permission'] ?? null;
                $canViewChild = !$cp || (auth()->check() && auth()->user()->can($cp));
                if ($canViewChild && !empty($child['route']) && Route::has($child['route'])) {
                    return route($child['route']);
                }
            }
            return '#';
        }
        return !empty($item['route']) && Route::has($item['route']) ? route($item['route']) : '#';
    };

    /**
     * Check if a top-level item should be visible.
     */
    $canViewItem = function (array $item) {
        $permission = $item['permission'] ?? null;
        if ($permission && !(auth()->check() && auth()->user()->can($permission))) {
            return false;
        }
        // If item has children, show it only when at least one child is accessible
        $children = $item['children'] ?? [];
        if (!empty($children)) {
            foreach ($children as $child) {
                $cp = $child['permission'] ?? null;
                if (!$cp || (auth()->check() && auth()->user()->can($cp))) {
                    return true;
                }
            }
            return false;
        }
        return true;
    };
@endphp

<!-- Mobile overlay -->
<div x-show="sidebarOpen" x-transition.opacity x-cloak class="md:hidden z-40 fixed inset-0 bg-black/50"
    @click="sidebarOpen = false">
</div>

<!-- Sidebar -->
<aside
    class="lg:top-0 left-0 z-50 lg:z-auto lg:static fixed lg:sticky inset-y-0 flex flex-col bg-[#0c0e12] border-zinc-800/60 border-r w-52 lg:h-screen transition-transform -translate-x-full lg:translate-x-0 duration-300 transform"
    :class="sidebarOpen ? 'translate-x-0' : ''">

    <!-- Logo -->
    <div class="flex items-center px-4 h-16 shrink-0">
        <x-brand-logo size="h-8" />
    </div>

    <!-- Main Navigation -->
    <nav class="flex-1 space-y-0.5 px-2 py-2 overflow-y-auto">
        @foreach ($mainItems as $item)
            @php
                if (!$canViewItem($item)) {
                    continue;
                }
                $isActive = $isItemActive($item);
                $url = $resolveUrl($item);
                $icon = $item['icon'] ?? null;
            @endphp

            <a href="{{ $url }}" wire:navigate
                class="group relative flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium
                      transition-colors cursor-pointer
                      {{ $isActive ? 'bg-white/10 text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">

                {{-- Active left accent bar --}}
                @if ($isActive)
                    <span class="top-1/2 left-0 absolute bg-blue-500 rounded-r-full w-0.5 h-5 -translate-y-1/2"></span>
                @endif

                @if ($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4 shrink-0" />
                @else
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-zinc-500 shrink-0" />
                @endif

                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <!-- Bottom: Settings etc. -->
    <div class="space-y-0.5 px-2 py-2 border-zinc-800/60 border-t shrink-0">
        @foreach ($bottomItems as $item)
            @php
                if (!$canViewItem($item)) {
                    continue;
                }
                $isActive = $isItemActive($item);
                $url = $resolveUrl($item);
                $icon = $item['icon'] ?? null;
            @endphp

            <a href="{{ $url }}" wire:navigate
                class="group relative flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium
                      transition-colors cursor-pointer
                      {{ $isActive ? 'bg-white/10 text-white' : 'text-zinc-400 hover:bg-white/5 hover:text-white' }}">

                @if ($isActive)
                    <span class="top-1/2 left-0 absolute bg-blue-500 rounded-r-full w-0.5 h-5 -translate-y-1/2"></span>
                @endif

                @if ($icon)
                    <x-dynamic-component :component="$icon" class="w-4 h-4 shrink-0" />
                @else
                    <x-heroicon-o-squares-2x2 class="w-4 h-4 text-zinc-500 shrink-0" />
                @endif

                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</aside>
