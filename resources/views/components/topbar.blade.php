@props(['title' => 'Dashboard'])

<header
    class="top-0 z-40 sticky flex justify-between items-center bg-topbar px-4 border-surface border-b h-16 transition-colors duration-300">
    <!-- Left -->
    <div class="flex items-center gap-3">
        <!-- Burger -->
        <button @click="sidebarOpen = true" class="md:hidden text-fg-muted hover:text-fg transition-colors">
            <x-heroicon-o-bars-3 class="w-6 h-6" />
        </button>

        <h1 class="ml-4 font-semibold text-fg text-lg">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
        @auth
            {{-- Agent controls: status + calling --}}
            <x-agent-control-bar />
        @endauth

        <!-- Notifications -->
        <button class="text-fg-muted hover:text-fg transition-colors">
            <x-heroicon-o-bell class="w-6 h-6" />
        </button>

        {{-- Theme toggle --}}
        <button @click="$store.theme.toggle()" title="Toggle light / dark mode"
            class="flex items-center justify-center w-8 h-8 rounded-lg text-fg-muted hover:text-fg hover:bg-surface-2 transition-colors">
            <x-heroicon-o-sun class="w-5 h-5" x-show="$store.theme.isDark" />
            <x-heroicon-o-moon class="w-5 h-5" x-show="!$store.theme.isDark" x-cloak />
        </button>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <!-- Avatar -->
            <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                <span
                    class="inline-flex justify-center items-center bg-avatar rounded-full w-9 h-9 font-medium text-avatar text-sm transition-colors cursor-pointer">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </span>
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="right-0 z-50 absolute bg-surface shadow-lg mt-2 border border-surface rounded-lg w-56">
                <!-- User info -->
                <div class="px-4 py-3 border-surface border-b">
                    <p class="font-medium text-fg text-sm">
                        {{ auth()->user()->name ?? 'Demo User' }}
                    </p>
                    <p class="text-fg-muted text-xs">
                        {{ auth()->user()->email ?? 'demo@example.com' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 hover:bg-surface-2 px-4 py-2 w-full text-fg-muted text-sm transition-colors cursor-pointer">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
