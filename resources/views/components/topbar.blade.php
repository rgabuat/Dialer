@props(['title' => 'Dashboard'])

<header
    class="top-0 z-40 sticky flex justify-between items-center bg-white bg-gradient-to-b from-[#151a20] to-[#0f1115] shadow px-4 h-16">
    <!-- Left -->
    <div class="flex items-center gap-3">
        <!-- Burger -->
        <button @click="sidebarOpen = true" class="md:hidden text-gray-600 hover:text-gray-900">
            <x-heroicon-o-bars-3 class="w-6 h-6" />
        </button>

        <h1 class="font-semibold text-white text-lg">
            {{ $title ?? 'Dashboard' }}
        </h1>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-4">
        @auth
            {{-- Agent controls: status + calling --}}
            <x-agent-control-bar />
        @endauth

        <!-- Notifications (optional) -->
        <button class="text-gray-500 hover:text-gray-700">
            <x-heroicon-o-bell class="w-6 h-6" />
        </button>

        <!-- User dropdown -->
        <div x-data="{ open: false }" class="relative">
            <!-- Avatar -->
            <button @click="open = !open" class="flex items-center gap-2 focus:outline-none">
                <span
                    class="inline-flex justify-center items-center bg-gray-900 rounded-full w-9 h-9 font-medium text-white text-sm cursor-pointer">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
                </span>
            </button>

            <!-- Dropdown -->
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="right-0 z-50 absolute bg-white shadow-lg mt-2 rounded-lg w-56">
                <!-- User info -->
                <div class="px-4 py-3">
                    <p class="font-medium text-gray-800 text-sm">
                        {{ auth()->user()->name ?? 'Demo User' }}
                    </p>
                    <p class="text-gray-500 text-xs">
                        {{ auth()->user()->email ?? 'demo@example.com' }}
                    </p>
                </div>

                <!-- Actions -->
                <div class="py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="flex items-center gap-2 hover:bg-gray-100 px-4 py-2 w-full text-gray-700 text-sm cursor-pointer">
                            <x-heroicon-o-arrow-right-on-rectangle class="w-5 h-5" />
                            Sign out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
