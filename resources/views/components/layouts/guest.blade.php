<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Guest Area - csrpro' }}</title>
    @livewireStyles
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="bg-[#0f1115] min-h-screen flex items-center justify-center">
    {{ $slot }}

    @livewireScripts
</body>
</html>

