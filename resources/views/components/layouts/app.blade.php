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
                <flux:sidebar.brand href="{{ url('/'.app()->getLocale()) }}" name="rent2gether">
                    <x-app.brand-mark size="sm" />
                </flux:sidebar.brand>
            </flux:sidebar.header>

            <flux:sidebar.search placeholder="{{ __('navigation.search_workspace') }}" />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" href="{{ url('/'.app()->getLocale()) }}">{{ __('navigation.home') }}</flux:sidebar.item>
                <flux:sidebar.item icon="magnifying-glass" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.search') }}</flux:sidebar.item>
            </flux:sidebar.nav>

            @auth
            <flux:sidebar.nav heading="{{ __('navigation.guest') }}">
                <flux:sidebar.item icon="calendar-days" href="{{ route('guest.bookings.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.my_bookings') }}</flux:sidebar.item>
                <flux:sidebar.item icon="heart" href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.favorites') }}</flux:sidebar.item>
                <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.messages') }}</flux:sidebar.item>
                <flux:sidebar.item icon="bookmark" href="{{ route('saved-searches.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.saved_searches') }}</flux:sidebar.item>
                <flux:sidebar.item icon="clock" href="{{ route('waitlist.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.waitlist') }}</flux:sidebar.item>
            </flux:sidebar.nav>

            @if(auth()->user()->is_host)
                <flux:sidebar.nav heading="{{ __('navigation.host') }}">
                    <flux:sidebar.item icon="squares-2x2" href="{{ route('host.dashboard', ['locale' => app()->getLocale()]) }}">{{ __('navigation.dashboard') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" href="{{ route('host.properties.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.properties') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" href="{{ route('host.bookings.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.bookings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" href="{{ route('host.earnings', ['locale' => app()->getLocale()]) }}">{{ __('navigation.earnings') }}</flux:sidebar.item>
                </flux:sidebar.nav>
            @endif
            @endauth

            <flux:sidebar.spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="information-circle" href="#">{{ __('navigation.help') }}</flux:sidebar.item>
                @auth
                <flux:sidebar.item icon="user-circle" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}">{{ __('navigation.profile') }}</flux:sidebar.item>
                @endauth
            </flux:sidebar.nav>

            <flux:dropdown position="top" align="start" class="max-lg:hidden">
                <flux:sidebar.profile initials="R2" name="rent2gether" />

                <flux:menu>
                        <flux:menu.group heading="{{ __('navigation.workspace') }}">
                            <flux:menu.item icon="swatch">{{ __('navigation.brand') }}</flux:menu.item>
                            <flux:menu.item icon="command-line">{{ __('navigation.import') }}</flux:menu.item>
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
                <flux:breadcrumbs.item href="{{ url('/'.app()->getLocale()) }}">rent2gether</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $title ?? __('navigation.home') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:spacer />

            <x-app.locale-switcher />
            <x-app.appearance-menu />

            @auth
            <flux:dropdown align="end">
                <flux:profile :initials="Str::substr(auth()->user()->name, 0, 2)" circle />

                <flux:menu>
                    <flux:menu.item icon="user-circle" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}">{{ __('navigation.profile') }}</flux:menu.item>
                    <flux:menu.item icon="calendar-days" href="{{ route('guest.bookings.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.my_bookings') }}</flux:menu.item>
                    <flux:menu.item icon="heart" href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.favorites') }}</flux:menu.item>
                    <flux:menu.item icon="chat-bubble-left-right" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}">{{ __('navigation.messages') }}</flux:menu.item>
                    @if(auth()->user()->is_host)
                    <flux:menu.separator />
                    <flux:menu.item icon="squares-2x2" href="{{ route('host.dashboard', ['locale' => app()->getLocale()]) }}">{{ __('navigation.host_dashboard') }}</flux:menu.item>
                    @endif
                    <flux:menu.separator />
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <flux:menu.item icon="arrow-right-start-on-rectangle" type="submit">{{ __('navigation.logout') }}</flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
            @else
            <flux:button size="sm" href="{{ route('auth.login') }}">{{ __('navigation.login') }}</flux:button>
            @endauth
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
