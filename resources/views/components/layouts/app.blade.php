<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Client Area - csrpro' }}</title>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 m-0 h-screen min-h-screen overflow-hidden">

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

            <!-- Topbar -->
            <x-topbar title="Dashboard">
                <span class="text-gray-600 text-sm">
                    {{ auth()->user()->name ?? 'User' }}
                </span>
            </x-topbar>
            <!-- Page content -->
            <main class="flex-1 bg-[#0f1115] p-2 md:p-4 lg:p-6 overflow-y-auto">
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
