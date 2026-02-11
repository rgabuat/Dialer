<header class="sticky top-0 z-40 h-16 bg-white shadow flex items-center justify-between px-4 bg-gradient-to-b from-[#151a20] to-[#0f1115]">
    <!-- Left -->
    <div class="flex items-center gap-3">
        <!-- Burger -->
        <button
            @click="sidebarOpen = true"
            class="md:hidden text-gray-600 hover:text-gray-900"
        >
            <x-heroicon-o-bars-3 class="w-6 h-6" />
        </button>

        <h1 class="text-lg font-semibold text-white">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
        <!-- Notifications (optional) -->
        <button class="text-gray-500 hover:text-gray-700">
            <x-heroicon-o-bell class="w-6 h-6" />
        </button>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <!-- Avatar -->
            <button
                @click="open = !open"
                class="flex items-center gap-2 focus:outline-none"
            >
                <span
                    class="inline-flex items-center justify-center
                           w-9 h-9 rounded-full bg-gray-900 text-white text-sm font-medium cursor-pointer"
                >
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </span>
            </button>

            <!-- Dropdown -->
            <div
                x-show="open"
                x-transition
                x-cloak
                @click.outside="open = false"
                class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg  z-50"
            >
                <!-- User info -->
                <div class="px-4 py-3 ">
                    <p class="text-sm font-medium text-gray-800">
                        {{ auth()->user()->name ?? 'Demo User' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ auth()->user()->email ?? 'demo@example.com' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2
                                   text-sm text-gray-700 hover:bg-gray-100 
                                   cursor-pointer"
                        >
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
