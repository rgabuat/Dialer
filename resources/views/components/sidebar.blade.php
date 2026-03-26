@php
    $items = config('navitems');
@endphp
<!-- Mobile overlay -->
<div
    x-show="sidebarOpen"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 bg-black/50 z-40 md:hidden"
    @click="sidebarOpen = false"
></div>

<!-- Sidebar -->
<aside
    class="
        fixed inset-y-0 left-0 z-50
        w-64
        bg-gradient-to-b from-[#151a20] to-[#0f1115] shadow
        flex flex-col
        transform transition-transform duration-300

        -translate-x-full
        lg:translate-x-0

        lg:static
        lg:sticky
        lg:top-0
        lg:h-screen
        lg:z-auto
        "
    :class="sidebarOpen ? 'translate-x-0' : ''"
>
    <!-- Logo -->
    <div class="h-16 flex items-center justify-center shrink-0 ">
        <x-brand-logo size="h-10" />
    </div>
    <hr class="border-neutral-800">
    <!-- Navigation (scrollable) -->
    <!-- Navigation -->
    <nav class="flex-1 overflow-y-auto p-4 space-y-1">

        @forelse ($items as $item)

            @php
                // SAFE defaults
                $label      = $item['label'] ?? 'Menu';
                $routeName  = $item['route'] ?? null;
                $segment    = $item['segment'] ?? null;
                $icon       = $item['icon'] ?? null;
                $permission = $item['permission'] ?? null;

                // Permission check
                $canView = true;
                if ($permission) {
                    $canView = auth()->check() && auth()->user()->can($permission);
                }

                // Active state (safe)
                $isActive =
                    ($routeName && request()->routeIs($routeName)) ||
                    ($segment && request()->segment(1) === $segment);

                // URL fallback
                $url = $routeName && Route::has($routeName)
                    ? route($routeName)
                    : '#';
            @endphp

            @if ($canView)
                <a
                    href="{{ $url }}"
                    wire:navigate
                    class="
                        flex items-center gap-3 px-3 py-2 rounded-lg
                        text-sm font-medium transition-colors cursor-pointer
                        {{ $isActive
                            ? 'bg-white/10 text-white'
                            : 'text-gray-400 hover:bg-white/5 hover:text-white'
                        }}"
                >
                    <!-- Icon (safe) -->
                    @if(!empty($icon))
                        <x-dynamic-component
                            :component="$icon"
                            class="w-5 h-5"
                        />
                    @else
                        <!-- Fallback icon -->
                        <x-heroicon-o-squares-2x2 class="w-5 h-5 text-gray-400" />
                    @endif

                    <span>{{ $label }}</span>
                </a>
            @endif

        @empty
            <p class="text-sm text-gray-400 px-3">
                No navigation items
            </p>
        @endforelse

    </nav>
</aside>
