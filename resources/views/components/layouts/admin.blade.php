<!doctype html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel — csrpro' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-base min-h-screen antialiased">

    {{-- ── Sidebar ── --}}
    <aside class="fixed inset-y-0 left-0 z-40 flex flex-col w-56 bg-surface border-r border-surface">

        {{-- Logo --}}
        <div class="flex items-center gap-2.5 px-5 h-14 border-b border-surface shrink-0">
            <span class="inline-flex justify-center items-center bg-indigo-600 rounded-lg w-7 h-7 shrink-0">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </span>
            <div class="leading-tight">
                <p class="font-bold text-fg text-sm">csrpro</p>
                <p class="text-[10px] text-fg-muted font-medium tracking-wide uppercase">Admin Panel</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                Dashboard
            </a>

            <a href="{{ route('admin.campaigns.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.campaigns.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                </svg>
                Campaigns
            </a>
        </nav>

        {{-- Footer --}}
        <div class="px-3 py-3 border-t border-surface shrink-0">
            <div class="flex items-center gap-2.5 px-2 py-1.5 mb-2">
                <span
                    class="inline-flex justify-center items-center bg-indigo-500/20 rounded-full w-7 h-7 font-bold text-indigo-400 text-xs shrink-0">
                    {{ strtoupper(substr(auth()->user()->first_name ?? 'A', 0, 1)) }}
                </span>
                <div class="flex-1 min-w-0 leading-tight">
                    <p class="font-semibold text-fg text-xs truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-fg-muted truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 w-full px-3 py-2 rounded-lg text-xs font-medium text-fg-muted hover:text-red-400 hover:bg-red-500/10 transition">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15" />
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main ── --}}
    <div class="pl-56 flex flex-col min-h-screen">
        <header
            class="sticky top-0 z-30 flex items-center justify-between h-14 px-6 bg-surface border-b border-surface">
            <h1 class="font-semibold text-fg text-sm">{{ $heading ?? 'Admin' }}</h1>
            <span class="text-xs text-fg-muted">Super Admin</span>
        </header>

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>

</html>
