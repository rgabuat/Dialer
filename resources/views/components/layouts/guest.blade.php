<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Guest Area - ' . config('app.name') }}</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex justify-center items-center bg-base min-h-screen">
    {{ $slot }}

    @livewireScripts
</body>

</html>
