@php
    $bgClass = 'bg-base';
@endphp
<!doctype html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — csrpro</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-base flex items-center justify-center px-4 antialiased">

    <div class="w-full max-w-md">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-8">
            <span
                class="inline-flex justify-center items-center bg-indigo-600 rounded-2xl w-12 h-12 mb-3 shadow-lg shadow-indigo-500/20">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
            </span>
            <h1 class="text-fg font-bold text-xl tracking-tight">csrpro Admin</h1>
            <p class="text-fg-muted text-sm mt-1">Sign in to the administration panel</p>
        </div>

        {{-- Card --}}
        <div class="bg-surface border border-surface rounded-2xl shadow-2xl overflow-hidden">

            {{-- Header stripe --}}
            <div class="h-1 bg-gradient-to-r from-indigo-600 via-violet-500 to-indigo-600"></div>

            <div class="px-8 py-8">

                {{-- Error --}}
                @if ($errors->any())
                    <div
                        class="flex items-start gap-2.5 bg-red-500/10 border border-red-500/20 rounded-xl px-4 py-3 mb-6">
                        <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <p class="text-red-400 text-sm">{{ $errors->first() }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.post') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email"
                            class="block text-xs font-semibold text-fg-muted uppercase tracking-widest mb-2">
                            Email
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            value="{{ old('email') }}" placeholder="admin@example.com"
                            class="w-full bg-surface-2 border border-surface rounded-xl px-4 py-2.5 text-fg text-sm placeholder-fg-muted
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition" />
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password"
                            class="block text-xs font-semibold text-fg-muted uppercase tracking-widest mb-2">
                            Password
                        </label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            placeholder="••••••••"
                            class="w-full bg-surface-2 border border-surface rounded-xl px-4 py-2.5 text-fg text-sm placeholder-fg-muted
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500/50 focus:border-indigo-500/50 transition" />
                    </div>

                    {{-- Remember --}}
                    <div class="flex items-center gap-2">
                        <input id="remember" name="remember" type="checkbox"
                            class="w-3.5 h-3.5 rounded border-surface bg-surface-2 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-0">
                        <label for="remember" class="text-sm text-fg-muted select-none">Remember me</label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm py-2.5 rounded-xl
                               shadow-sm transition-all duration-150 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                        Sign in to Admin Panel
                    </button>
                </form>

            </div>
        </div>

        <p class="text-center text-xs text-fg-muted mt-6">
            <a href="{{ route('login') }}" class="hover:text-fg transition">← Back to agent login</a>
        </p>

    </div>

</body>

</html>
