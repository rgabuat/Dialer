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

<body class="bg-gray-100 min-h-screen h-screen m-0 overflow-hidden">

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
                            }
                        }));
                    }
                });
        "
        @endauth
        class="flex h-full">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main column -->
        <div class="flex-1 flex flex-col">

            <!-- Topbar -->
            <x-topbar title="Dashboard">
                <span class="text-sm text-gray-600">
                    {{ auth()->user()->name ?? 'User' }}
                </span>
            </x-topbar>
            <div class="flex items-center justify-center py-2">
                {{-- AGENT CONTROL BAR --}}
                <div wire:ignore x-data="agentPhone()"
                    class="mb-2 flex items-center justify-between gap-4
           rounded-xl border border-zinc-800 bg-zinc-900
           px-4 py-3">

                    {{-- LEFT: STATUS DROPDOWN --}}
                    <div>
                        @livewire('agent.status-switcher')
                    </div>

                    {{-- RIGHT: CALL CONTROLS --}}
                    <div class="flex items-center gap-3">

                        {{-- ENABLE CALLING --}}
                        <button @click="enableCalling" :disabled="!canAcceptCalls"
                            class="rounded-lg px-5 py-2 text-sm font-semibold transition
                   shadow
                   "
                            :class="canAcceptCalls
                                ?
                                'bg-blue-600 text-white hover:bg-blue-500' :
                                'bg-zinc-800 text-zinc-500 cursor-not-allowed'">
                            Enable Calling
                        </button>

                        {{-- DIAL --}}
                        <button @click="openDialer" :disabled="!deviceReady"
                            class="rounded-lg px-4 py-2 text-sm font-semibold transition"
                            :class="deviceReady
                                ?
                                'bg-zinc-800 text-zinc-200 hover:bg-zinc-700' :
                                'bg-zinc-900 text-zinc-500 cursor-not-allowed'">
                            Dial
                        </button>

                    </div>
                </div>

                {{-- DIAL MODAL --}}
                <div x-data="{ open: false }" x-on:open-dialer.window="open = true" x-show="open" x-cloak
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">

                    <div
                        class="w-full max-w-sm rounded-xl bg-zinc-900
                border border-zinc-800 p-6 shadow-xl">

                        <h2 class="mb-4 text-lg font-semibold text-zinc-100">
                            Outbound Call
                        </h2>

                        <input x-ref="dialNumber" type="tel" placeholder="+1234567890"
                            class="mb-4 w-full rounded-lg bg-zinc-800
                   border border-zinc-700 px-4 py-2
                   text-zinc-100 placeholder-zinc-500
                   focus:outline-none focus:ring-2 focus:ring-blue-600" />

                        <div class="flex justify-end gap-3">
                            <button @click="open = false"
                                class="rounded-lg bg-zinc-800 px-4 py-2
                       text-sm text-zinc-300 hover:bg-zinc-700">
                                Cancel
                            </button>

                            <button @click="$dispatch('make-call', $refs.dialNumber.value)"
                                class="rounded-lg bg-green-600 px-4 py-2
                       text-sm font-semibold text-white
                       hover:bg-green-500">
                                Call
                            </button>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-2 md:p-4 lg:p-6 bg-[#0f1115]">
                {{ $slot }}
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts

    <x-toast />
    @stack('scripts')

    <script>
        function agentPhone() {
            return {
                token: null,
                identity: null,
                device: null,
                deviceReady: false,
                canAcceptCalls: @json(optional(auth()->user()->agentStatus?->statusType)->is_available ?? false),

                async enableCalling() {
                    if (!this.canAcceptCalls) {
                        alert('You are not accepting calls in your current status.');
                        return;
                    }

                    if (this.device) return;

                    try {
                        const res = await fetch('{{ route('twilio.getAccessToken') }}');
                        const data = await res.json();

                        this.token = data.token;
                        this.identity = data.identity;

                        this.initializeDevice();
                    } catch (e) {
                        console.error('Failed to get token', e);
                    }
                },

                initializeDevice() {
                    this.device = new window.Device(this.token, {
                        codecPreferences: ['opus', 'pcmu'],
                        logLevel: 1,
                    });

                    this.device.on('ready', () => {
                        this.deviceReady = true;
                        console.log('Twilio Device ready');
                    });

                    this.device.on('error', error => {
                        console.error('Twilio error', error);
                    });

                    this.device.register();
                },

                openDialer() {
                    if (!this.deviceReady) {
                        alert('Enable calling first.');
                        return;
                    }
                    window.dispatchEvent(new CustomEvent('open-dialer'));
                },

                async makeCall(number) {
                    if (!this.device || !number) return;

                    await this.device.connect({
                        params: {
                            To: number,
                            agent: this.identity,
                            From: '{{ config('services.twilio.caller_id') }}',
                        }
                    });
                }
            }
        }

        window.addEventListener('make-call', e => {
            document.querySelector('[x-data]').__x.$data.makeCall(e.detail);
        });
    </script>




</body>

</html>
