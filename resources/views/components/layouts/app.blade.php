<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Prevent theme flash: apply class before paint --}}
    <script>
        (function() {
            var t = localStorage.getItem('theme');
            document.documentElement.classList.add(t === 'light' ? 'light' : 'dark');
        })();
    </script>

    {{-- Alpine theme store (registered before Alpine boots) --}}
    <script>
        document.addEventListener('alpine:init', function() {
            Alpine.store('theme', {
                isDark: localStorage.getItem('theme') !== 'light',
                toggle: function() {
                    this.isDark = !this.isDark;
                    var cls = document.documentElement.classList;
                    cls.toggle('dark', this.isDark);
                    cls.toggle('light', !this.isDark);
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            });
        });
    </script>

    <title>{{ $title ?? 'Client Area - csrpro' }}</title>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-zinc-100 dark:bg-[#0f1115] m-0 h-screen min-h-screen overflow-hidden transition-colors duration-300">

    <div x-data="{ sidebarOpen: false }"
        @auth
x-init="
            const activeCampaignId = {{ session('active_campaign_id', 0) }};
            const currentUserId    = {{ auth()->id() }};

            // Logout this tab if the active campaign was deleted
            window.Echo.private('App.Models.User.' + currentUserId)
                .listen('.CampaignDeleted', function (e) {
                    if (activeCampaignId && parseInt(e.campaign_id) === parseInt(activeCampaignId)) {
                        fetch('{{ route('logout') }}', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                        }).finally(function () {
                            window.location.href = '{{ route('login') }}';
                        });
                    }
                });

            // Sync the status switcher button across all open tabs via broadcast
            window.Echo.private('agent-status')
                .listen('.AgentStatusUpdated', function (e) {
                    if (e.user_id === currentUserId) {
                        window.dispatchEvent(new CustomEvent('agent-status-changed', {
                            detail: {
                                startedAt:   e.started_at,
                                statusName:  e.status_name,
                                statusColor: e.status_color,
                                isAvailable: e.is_available,
                            }
                        })); 
                    }
                });
        " @endauth
        class="flex h-full">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main column -->
        <div class="flex flex-col flex-1">
            @php
                // Resolve the active nav item label for the topbar title
                $navItems = config('navitems');
                $currentSegment = request()->segment(1);
                $activeNavLabel = 'Dashboard';

                foreach ($navItems as $_item) {
                    if (!empty($_item['route']) && request()->routeIs($_item['route'])) {
                        $activeNavLabel = $_item['label'];
                        break;
                    }
                    foreach ($_item['segments'] ?? [] as $_seg) {
                        if ($currentSegment === $_seg) {
                            $activeNavLabel = $_item['label'];
                            break 2;
                        }
                    }
                    foreach ($_item['children'] ?? [] as $_child) {
                        if (!empty($_child['segment']) && $currentSegment === $_child['segment']) {
                            $activeNavLabel = $_item['label'];
                            break 2;
                        }
                        if (!empty($_child['route']) && request()->routeIs($_child['route'])) {
                            $activeNavLabel = $_item['label'];
                            break 2;
                        }
                    }
                }
            @endphp

            <!-- Topbar -->
            <x-topbar :title="$activeNavLabel" />

            <!-- Section sub-navigation (tabs) -->
            <x-subnav />

            <!-- Page content -->
            <main
                class="flex-1 bg-zinc-100 dark:bg-[#0f1115] p-2 md:p-4 lg:p-6 overflow-y-auto transition-colors duration-300">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts

    <x-toast />
    @stack('scripts')


</body>

</html>
