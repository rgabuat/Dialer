@props(['title' => 'Dashboard'])

<header
    class="top-0 z-40 sticky flex justify-between items-center bg-white dark:bg-gradient-to-b dark:from-[#151a20] dark:to-[#0f1115] px-4 border-zinc-200 dark:border-zinc-800/40 border-b h-16 transition-colors duration-300">
    <!-- Left -->
    <div class="flex items-center gap-3">
        <!-- Burger -->
        <button @click="sidebarOpen = true"
            class="md:hidden text-zinc-600 hover:text-zinc-900 dark:hover:text-zinc-200 dark:text-zinc-400 transition-colors">
            <x-heroicon-o-bars-3 class="w-6 h-6" />
        </button>

        <h1 class="ml-4 font-semibold text-zinc-900 dark:text-white text-lg">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
        @auth
            {{-- Agent controls: status + calling --}}
            <x-agent-control-bar />
        @endauth

        <!-- Theme toggle -->
        <button @click="$store.theme.toggle()"
            class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-100 dark:text-zinc-400 transition-colors"
            title="Toggle theme">
            <x-heroicon-o-sun class="w-5 h-5" x-show="$store.theme.isDark" />
            <x-heroicon-o-moon class="w-5 h-5" x-show="!$store.theme.isDark" />
        </button>

        <!-- Notifications -->
        <button class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-200 dark:text-zinc-400 transition-colors">
            <x-heroicon-o-bell class="w-6 h-6" />
        </button>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <!-- Avatar -->
            <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                <span
                    class="inline-flex justify-center items-center bg-zinc-200 dark:bg-zinc-800 rounded-full w-9 h-9 font-medium text-zinc-800 dark:text-white text-sm transition-colors cursor-pointer">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </span>
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="right-0 z-50 absolute bg-white dark:bg-zinc-900 shadow-lg dark:shadow-zinc-950/60 mt-2 border border-zinc-100 dark:border-zinc-800 rounded-lg w-56">
                <!-- User info -->
                <div class="px-4 py-3 border-zinc-100 dark:border-zinc-800 border-b">
                    <p class="font-medium text-zinc-800 dark:text-zinc-100 text-sm">
                        {{ auth()->user()->name ?? 'Demo User' }}
                    </p>
                    <p class="text-zinc-500 dark:text-zinc-400 text-xs">
                        {{ auth()->user()->email ?? 'demo@example.com' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 hover:bg-zinc-50 dark:hover:bg-zinc-800 px-4 py-2 w-full text-zinc-700 dark:text-zinc-300 text-sm transition-colors cursor-pointer">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
