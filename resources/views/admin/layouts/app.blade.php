<!doctype html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Admin Panel — ' . config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
    <style>
        /* CKEditor dark-mode overrides */
        .ck.ck-editor__main>.ck-editor__editable,
        .ck.ck-toolbar {
            background: var(--color-surface-2, #1e1e2e) !important;
            color: var(--color-fg, #e4e4ef) !important;
            border-color: var(--color-surface, #2a2a3a) !important;
        }

        .ck.ck-toolbar {
            border-bottom-color: var(--color-surface, #2a2a3a) !important;
        }

        .ck.ck-button,
        .ck.ck-button .ck-icon {
            color: var(--color-fg-muted, #a0a0b8) !important;
        }

        .ck.ck-button:hover,
        .ck.ck-button.ck-on {
            background: var(--color-hover, #2e2e40) !important;
            color: var(--color-fg, #e4e4ef) !important;
        }

        .ck.ck-editor {
            border-radius: 0.5rem !important;
            overflow: hidden;
            border: 1px solid var(--color-surface, #2a2a3a) !important;
        }

        .ck.ck-editor__main>.ck-editor__editable {
            min-height: 200px;
        }

        .ck.ck-editor__editable p {
            margin: 0 0 0.5em;
        }
    </style>
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
                <p class="font-bold text-fg text-sm">{{ config('app.name') }}</p>
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

            <a href="{{ route('admin.lead-templates.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.lead-templates.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                </svg>
                Lead Templates
            </a>

            <a href="{{ route('admin.cid-groups.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.cid-groups.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 8.25h3m-3 3h3m-3 3h3M6.75 20.25v.008h.008v-.008H6.75Z" />
                </svg>
                CID Groups
            </a>

            <a href="{{ route('admin.cid-numbers.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.cid-numbers.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6v.75Z" />
                </svg>
                CID Numbers
            </a>

            <a href="{{ route('admin.in-groups.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('admin.in-groups.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                Inbound Groups
            </a>

            {{-- People --}}
            <p class="px-3 pt-4 pb-1 text-[10px] font-semibold uppercase tracking-wider text-fg-muted">People</p>

            <a href="{{ route('users.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('users.*') || request()->routeIs('user.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Users
            </a>

            <a href="{{ route('roles.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('roles.*') || request()->routeIs('role.*') || request()->routeIs('permissions.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                Roles & Permissions
            </a>

            <a href="{{ route('user-groups.index') }}"
                class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium transition
                       {{ request()->routeIs('user-groups.*') || request()->routeIs('user-group.*') ? 'bg-indigo-600/15 text-indigo-400' : 'text-fg-muted hover:text-fg hover:bg-hover' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                </svg>
                User Groups
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

    {{-- ── Main content ── --}}
    <div class="pl-56 flex flex-col min-h-screen">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-30 flex items-center justify-between h-14 px-6 bg-surface border-b border-surface">
            <h1 class="font-semibold text-fg text-sm">{{ $heading ?? 'Dashboard' }}</h1>
            <span class="text-xs text-fg-muted">Super Admin</span>
        </header>

        {{-- Page --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>
    </div>

    @livewireScripts
</body>

</html>
