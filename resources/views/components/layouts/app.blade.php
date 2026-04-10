<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Prevent theme flash: apply class before paint + watch for Livewire morph wiping it --}}
    <script>
        (function() {
            function applyTheme() {
                var isDark = localStorage.getItem('theme') !== 'light';
                document.documentElement.classList.toggle('dark', isDark);
                document.documentElement.classList.toggle('light', !isDark);
            }
            applyTheme();

            // MutationObserver keeps the theme class intact when Livewire morphs <html> during wire:navigate
            var _themeObserver = new MutationObserver(function() {
                var isDark = localStorage.getItem('theme') !== 'light';
                var el = document.documentElement;
                var hasDark = el.classList.contains('dark');
                var hasLight = el.classList.contains('light');
                if ((isDark && !hasDark) || (!isDark && !hasLight)) {
                    _themeObserver.disconnect();
                    applyTheme();
                    _themeObserver.observe(el, {
                        attributes: true,
                        attributeFilter: ['class']
                    });
                }
            });
            _themeObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class']
            });
        })();
    </script>

    {{-- Alpine theme store (registered before Alpine boots) --}}
    <script>
        document.addEventListener('alpine:init', function() {
            // Count-up animation for stat numbers
            Alpine.data('countUp', function(target, duration) {
                duration = duration || 700;
                return {
                    val: '0',
                    init: function() {
                        var self = this;
                        var start = performance.now();
                        var tick = function(now) {
                            var t = Math.min((now - start) / duration, 1);
                            var ease = 1 - Math.pow(1 - t, 3);
                            self.val = Math.round(target * ease).toString();
                            if (t < 1) requestAnimationFrame(tick);
                        };
                        requestAnimationFrame(tick);
                    }
                };
            });

            // Count-up animation for MM:SS time values
            Alpine.data('countUpTime', function(targetMins, targetSecs, duration) {
                duration = duration || 700;
                var totalSecs = targetMins * 60 + targetSecs;
                return {
                    val: '00:00',
                    init: function() {
                        var self = this;
                        var start = performance.now();
                        var tick = function(now) {
                            var t = Math.min((now - start) / duration, 1);
                            var ease = 1 - Math.pow(1 - t, 3);
                            var cur = Math.round(totalSecs * ease);
                            var m = Math.floor(cur / 60);
                            var s = cur % 60;
                            self.val = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                            if (t < 1) requestAnimationFrame(tick);
                        };
                        requestAnimationFrame(tick);
                    }
                };
            });

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

        // Keep Alpine store in sync after wire:navigate
        document.addEventListener('livewire:navigated', function() {
            if (window.Alpine && Alpine.store('theme')) {
                Alpine.store('theme').isDark = localStorage.getItem('theme') !== 'light';
            }
        });
    </script>

    <title>{{ $title ?? 'Client Area - csrpro' }}</title>

    {{-- Livewire styles --}}
    @livewireStyles

    {{-- Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Navigate progress bar --}}
    <style>
        #nprogress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 2px;
            width: 0%;
            background: linear-gradient(90deg, #6366f1, #818cf8);
            z-index: 9999;
            transition: width .25s ease, opacity .5s ease;
            box-shadow: 0 0 8px #6366f160;
            border-radius: 0 2px 2px 0;
            pointer-events: none;
        }

        #nprogress-bar.done {
            width: 100% !important;
            opacity: 0;
        }
    </style>
    <script>
        (function() {
            var bar = null;
            var timer = null;

            function getBar() {
                if (!bar) {
                    bar = document.getElementById('nprogress-bar');
                }
                return bar;
            }

            function start() {
                var b = getBar();
                if (!b) return;
                b.classList.remove('done');
                b.style.opacity = '1';
                var w = 0;
                clearInterval(timer);
                timer = setInterval(function() {
                    w = w < 70 ? w + Math.random() * 8 : w < 90 ? w + 1 : w;
                    b.style.width = w + '%';
                }, 120);
            }

            function done() {
                var b = getBar();
                if (!b) return;
                clearInterval(timer);
                b.style.width = '100%';
                setTimeout(function() {
                    b.classList.add('done');
                    b.style.width = '0%';
                }, 400);
            }
            document.addEventListener('livewire:navigate', start);
            document.addEventListener('livewire:navigated', done);
        })();
    </script>
</head>

<body class="bg-base m-0 h-screen min-h-screen overflow-hidden text-fg transition-colors duration-300">

    {{-- Navigate progress bar element --}}
    <div id="nprogress-bar"></div>

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
                                startedAt:        e.started_at,
                                statusName:       e.status_name,
                                statusColor:      e.status_color,
                                isAvailable:      e.is_available,
                                handles_inbound:  e.handles_inbound  ?? false,
                                handles_outbound: e.handles_outbound ?? false,
                            }
                        }));
                    }
                });
        " @endauth
        class="flex h-full">
        <!-- Sidebar -->
        <x-sidebar />

        <!-- Main column -->
        <div class="flex flex-col flex-1 min-w-0">
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
            <main class="flex-1 bg-base p-2 md:p-4 lg:p-6 overflow-y-auto transition-colors duration-300"
                style="scrollbar-gutter: stable" x-data
                x-on:livewire:navigated.document="$el.classList.remove('animate-fade-up'); void $el.offsetWidth; $el.classList.add('animate-fade-up')">
                <div class="animate-fade-up">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    {{-- Livewire scripts --}}
    @livewireScripts

    <x-toast />
    @stack('scripts')

    {{-- ── Frontend diagnostic logger ─────────────────────────────────────
         Captures JS errors, unhandled promise rejections, and Livewire
         errors and POSTs them to /api/client-log so they appear in
         storage/logs/laravel.log alongside server-side events.
    ──────────────────────────────────────────────────────────────────── --}}
    <script>
        (function() {
            var _csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            var _endpoint = '/api/client-log';
            var _sending = false;
            var _queue = [];
            var _MAX_QUEUE = 20; // don't flood on error storms
            var _FLUSH_MS = 500; // batch within 500 ms

            function sanitize(v) {
                if (v === null || v === undefined) return null;
                return String(v).slice(0, 2000);
            }

            function send(level, message, ctx) {
                if (_queue.length >= _MAX_QUEUE) return;
                _queue.push({
                    level: level,
                    message: sanitize(message),
                    context: ctx || {}
                });
                if (_sending) return;
                _sending = true;
                setTimeout(flush, _FLUSH_MS);
            }

            function flush() {
                var batch = _queue.splice(0);
                _sending = false;
                batch.forEach(function(entry) {
                    try {
                        fetch(_endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': _csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({
                                level: entry.level,
                                message: entry.message,
                                url: window.location.href,
                                context: entry.context,
                            }),
                            keepalive: true,
                        }).catch(function() {
                            /* silently ignore network failures */ });
                    } catch (e) {
                        /* never throw from the logger itself */ }
                });
            }

            // ── 1. Uncaught JS errors ─────────────────────────────────────────
            window.addEventListener('error', function(event) {
                send('error', event.message || 'Uncaught error', {
                    source: event.filename,
                    line: event.lineno,
                    col: event.colno,
                    stack: event.error ? String(event.error.stack || '').slice(0, 1000) : null,
                });
            });

            // ── 2. Unhandled promise rejections ──────────────────────────────
            window.addEventListener('unhandledrejection', function(event) {
                var reason = event.reason;
                var msg = (reason && reason.message) ? reason.message : String(reason ||
                    'Unhandled promise rejection');
                send('error', msg, {
                    stack: (reason && reason.stack) ? String(reason.stack).slice(0, 1000) : null,
                });
            });

            // ── 3. Livewire errors ────────────────────────────────────────────
            // Livewire v3 dispatches 'livewire:error' on $wire and also emits
            // a custom event on the document when a component update fails.
            document.addEventListener('livewire:error', function(event) {
                var detail = event.detail || {};
                send('error', '[Livewire] Component error', {
                    component: detail.component || null,
                    status: detail.status || null,
                    message: sanitize(detail.message || detail.response || ''),
                });
            });

            // Livewire v3 also hooks via Livewire.hook
            document.addEventListener('alpine:init', function() {
                if (window.Livewire && typeof Livewire.hook === 'function') {
                    try {
                        Livewire.hook('request.error', function(context) {
                            send('error', '[Livewire] Request error', {
                                status: context?.status,
                                message: sanitize(context?.response || ''),
                            });
                        });
                        Livewire.hook('commit.error', function(context) {
                            send('error', '[Livewire] Commit error', {
                                component: context?.component?.name,
                                message: sanitize(String(context?.error || '')),
                            });
                        });
                    } catch (e) {
                        /* Livewire hook API may vary */ }
                }
            });

            // ── 4. Wrap console.error so explicit app calls are captured too ─
            var _origConsoleError = console.error;
            console.error = function() {
                _origConsoleError.apply(console, arguments);
                try {
                    var parts = Array.prototype.slice.call(arguments).map(function(a) {
                        return typeof a === 'object' ? JSON.stringify(a) : String(a);
                    });
                    send('error', '[console.error] ' + parts.join(' '));
                } catch (e) {
                    /* ignore */ }
            };

            // ── 5. Expose a global helper for manual ad-hoc logging ──────────
            window._clientLog = function(level, message, ctx) {
                send(level || 'info', message, ctx);
            };
        })();
    </script>


</body>

</html>
