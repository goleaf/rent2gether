@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ? $title.' - '.config('app.name') : config('app.name') }}</title>

        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-zinc-50 dark:bg-zinc-950 font-sans antialiased flex items-center justify-center p-4">

        <div class="w-full max-w-sm">
            <div class="mb-8 text-center">
                <a href="{{ url('/'.app()->getLocale()) }}" class="inline-flex items-center gap-2 text-zinc-900 dark:text-white hover:opacity-80 transition-opacity">
                    <flux:icon name="home" class="size-6" />
                    <span class="text-xl font-semibold">rent2gether</span>
                </a>
            </div>

            {{ $slot }}
        </div>

        @livewireScripts
        @fluxScripts
    </body>
</html>
