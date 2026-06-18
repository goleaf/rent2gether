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
    <body class="min-h-screen overflow-x-hidden bg-white font-sans text-zinc-950 antialiased dark:bg-zinc-950 dark:text-white">
        <flux:sidebar sticky class="hidden border-e border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-zinc-900 lg:flex">
            <flux:sidebar.header>
                <flux:sidebar.brand href="{{ url('/') }}" name="rent2gether">
                    <x-app.brand-mark size="sm" />
                </flux:sidebar.brand>
            </flux:sidebar.header>

            <flux:sidebar.search placeholder="Search workspace..." />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" href="{{ url('/') }}" current>Overview</flux:sidebar.item>
                <flux:sidebar.item icon="calendar" href="#" badge="6">Bookings</flux:sidebar.item>
                <flux:sidebar.item icon="building-office-2" href="#">Spaces</flux:sidebar.item>
                <flux:sidebar.item icon="users" href="#">Members</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:sidebar.group expandable heading="Operations" icon="wrench-screwdriver">
                <flux:sidebar.item icon="check-circle" href="#">Verification</flux:sidebar.item>
                <flux:sidebar.item icon="map-pin" href="#">Locations</flux:sidebar.item>
                <flux:sidebar.item icon="document-text" href="#">Documents</flux:sidebar.item>
            </flux:sidebar.group>

            <flux:sidebar.spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="cube" href="#">Reviews</flux:sidebar.item>
                <flux:sidebar.item icon="information-circle" href="#">Help</flux:sidebar.item>
                <flux:sidebar.item icon="cog-6-tooth" href="#">Settings</flux:sidebar.item>
            </flux:sidebar.nav>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile initials="R2" name="rent2gether" />

                <flux:menu>
                    <flux:menu.group heading="Workspace">
                        <flux:menu.item icon="swatch">Brand setup</flux:menu.item>
                        <flux:menu.item icon="command-line">Data import</flux:menu.item>
                    </flux:menu.group>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <flux:header sticky class="!min-h-14 border-b border-zinc-200 bg-white/95 !px-3 backdrop-blur dark:border-white/10 dark:bg-zinc-950/95 sm:!px-4 lg:!px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2 lg:hidden">
                <x-app.brand-mark size="sm" />
                <span class="truncate text-sm font-medium text-zinc-800 dark:text-white">rent2gether</span>
            </a>

            <flux:breadcrumbs class="hidden lg:flex">
                <flux:breadcrumbs.item href="{{ url('/') }}">rent2gether</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $title ?? 'Overview' }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:spacer />

            <x-app.appearance-menu />

            <flux:dropdown align="end">
                <flux:profile initials="R2" circle />

                <flux:menu>
                    <flux:menu.item icon="home" href="{{ url('/') }}">Overview</flux:menu.item>
                    <flux:menu.item icon="cog-6-tooth">Settings</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        <flux:main container class="!px-4 !py-4 !pb-24 sm:!px-5 sm:!py-5 lg:!px-8 lg:!py-8">
            {{ $slot }}
        </flux:main>

        <x-app.mobile-nav />

        <flux:toast />

        @livewireScripts
        @fluxScripts
    </body>
</html>
