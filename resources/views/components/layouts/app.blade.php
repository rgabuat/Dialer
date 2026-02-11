<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Client Area - csrpro' }}</title>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen h-screen m-0 overflow-hidden">

    <div x-data="{ sidebarOpen: false }" class="flex h-full">
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
<div
    x-data="agentPhone()"
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
        <button
            @click="enableCalling"
            :disabled="!canAcceptCalls"
            class="rounded-lg px-5 py-2 text-sm font-semibold transition
                   shadow
                   "
            :class="canAcceptCalls
                ? 'bg-blue-600 text-white hover:bg-blue-500'
                : 'bg-zinc-800 text-zinc-500 cursor-not-allowed'">
            Enable Calling
        </button>

        {{-- DIAL --}}
        <button
            @click="openDialer"
            :disabled="!deviceReady"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition"
            :class="deviceReady
                ? 'bg-zinc-800 text-zinc-200 hover:bg-zinc-700'
                : 'bg-zinc-900 text-zinc-500 cursor-not-allowed'">
            Dial
        </button>

    </div>
</div>

                {{-- DIAL MODAL --}}
<div
    x-data="{ open: false }"
    x-on:open-dialer.window="open = true"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">

    <div class="w-full max-w-sm rounded-xl bg-zinc-900
                border border-zinc-800 p-6 shadow-xl">

        <h2 class="mb-4 text-lg font-semibold text-zinc-100">
            Outbound Call
        </h2>

        <input
            x-ref="dialNumber"
            type="tel"
            placeholder="+1234567890"
            class="mb-4 w-full rounded-lg bg-zinc-800
                   border border-zinc-700 px-4 py-2
                   text-zinc-100 placeholder-zinc-500
                   focus:outline-none focus:ring-2 focus:ring-blue-600" />

        <div class="flex justify-end gap-3">
            <button
                @click="open = false"
                class="rounded-lg bg-zinc-800 px-4 py-2
                       text-sm text-zinc-300 hover:bg-zinc-700">
                Cancel
            </button>

            <button
                @click="$dispatch('make-call', $refs.dialNumber.value)"
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


    // <script>
    //     // window.addEventListener('load', () => {
        //     console.log('window.Device =', window.Device);

        //     const device = new window.Device(null);
        //     console.log('Twilio loaded', device);
        // });

        /**
         * Global Variables
         */

    //     var token;
    //     var identity;
    //     var device;

    //     //Get twilio accesstoken
    //     document.getElementById('enableCall').addEventListener('click', () => {
    //         getTwilioAccessToken();
    //     });

    //     /**@arguments functions */

    //     //Setup twilio device
    //     async function getTwilioAccessToken() {
    //         console.log("Requesting Twilio access token...");

    //         try {
    //             //Get the data from our backend / accesstoken
    //             const response = await fetch('{{ route('twilio.getAccessToken') }}');
    //             const data = await response.json();

    //             token = data.token;
    //             identity = data.identity;

    //             initializeDevice();
    //             registerPhoneEvents();

    //             console.log("Received Twilio access token:", data);
    //             return data.token;
    //         } catch (error) {
    //             console.log("Error fetching Twilio access token:", error);
    //         }
    //     }

    //     async function initializeDevice() {
    //         if (!token) {
    //             console.warn('No token available');
    //             return;
    //         }

    //         if (device) {
    //             console.warn('Twilio Device already initialized');
    //             return;
    //         }

    //         console.log("Initializing Twilio Device with token:", token);

    //         device = new window.Device(token, {
    //             debug: true,
    //             codecPreferences: ['opus', 'pcmu'],
    //             logLevel: 1,
    //             maxCallSignalingTimeoutMs: 30000,
    //         });

    //         device.on('ready', () => {
    //             console.log('Twilio Device ready');
    //         });

    //         device.on('error', error => {
    //             console.error('Twilio Device error:', error);
    //         });

    //         device.register();
    //     }

    //     async function registerPhoneEvents() {
    //         const callNow = document.getElementById('callNow');
    //          const dialNumber = document.getElementById('dialNumber');

    //         callNow.addEventListener('click', async () => {

    //             //get number to call
    //             var phonNumber = dialNumber.value.trim();

    //             //set the twilio agent/customer 
    //             var params = {
    //                 To: phonNumber,
    //                 agent: identity,
    //                 From: '{{ config('services.twilio.caller_id') }}',
    //                 Location: 'US1'
    //             };

    //             //check if device exists before call
    //             if (device) {
    //                 //register cal levent listeners
    //                 const call = await device.connect({
    //                     params
    //                 })

    //                 //register hangup button listerner
    //             }

    //             // Close modal
    //             dialModal.classList.add('hidden');
    //             dialModal.classList.remove('flex');
    //         });
    //     }
    // </script>

    // <script>
    //     const dialModal = document.getElementById('dialModal');
    //     const openDialer = document.getElementById('openDialer');
    //     const closeDialer = document.getElementById('closeDialer');
       

    //     openDialer.addEventListener('click', () => {
    //         if (!device) {
    //             alert('Please enable calling first');
    //             return;
    //         }
    //         dialModal.classList.remove('hidden');
    //         dialModal.classList.add('flex');
    //     });

    //     closeDialer.addEventListener('click', () => {
    //         dialModal.classList.add('hidden');
    //         dialModal.classList.remove('flex');
    //     });
    // </script> --}}
    
</body>

</html>
