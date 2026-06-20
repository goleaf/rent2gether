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
        @persist('navigation-loading-bar')
            <div class="fixed inset-x-0 top-0 z-50 h-0.5 bg-transparent opacity-0 transition-opacity duration-150" wire:loading.delay.class.remove="opacity-0">
                <div class="h-full w-1/2 animate-pulse rounded-e-full bg-emerald-500"></div>
            </div>
        @endpersist

        <flux:sidebar sticky class="hidden border-e border-zinc-200 bg-zinc-50 dark:border-white/10 dark:bg-zinc-900 lg:flex">
            <flux:sidebar.header>
                <flux:sidebar.brand href="{{ route('home', ['locale' => app()->getLocale()]) }}" name="rent2gether" wire:navigate>
                    <x-app.brand-mark size="sm" />
                </flux:sidebar.brand>
            </flux:sidebar.header>

            <flux:sidebar.search placeholder="{{ __('navigation.search_workspace') }}" />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="home" href="{{ route('home', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.guest_home') }}</flux:sidebar.item>
                <flux:sidebar.item icon="magnifying-glass" href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.search') }}</flux:sidebar.item>
                <flux:sidebar.item icon="calendar-days" href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.trips') }}</flux:sidebar.item>
                <flux:sidebar.item icon="heart" href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.favorites') }}</flux:sidebar.item>
                <flux:sidebar.item icon="chat-bubble-left-right" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.messages') }}</flux:sidebar.item>
                <flux:sidebar.item icon="user-circle" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.profile') }}</flux:sidebar.item>
            </flux:sidebar.nav>

            @auth
            @if(auth()->user()->is_host)
                <flux:sidebar.nav heading="{{ __('navigation.host') }}">
                    <flux:sidebar.item icon="squares-2x2" href="{{ route('host.dashboard', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_home') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="building-office-2" href="{{ route('host.listings.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_listings') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="calendar-days" href="{{ route('host.calendar', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_calendar') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="clipboard-document-list" href="{{ route('host.requests.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_requests') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="banknotes" href="{{ route('host.income', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_income') }}</flux:sidebar.item>
                    <flux:sidebar.item icon="user-circle" href="{{ route('host.profile', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.profile') }}</flux:sidebar.item>
                </flux:sidebar.nav>
            @endif
            @endauth

            <flux:sidebar.spacer />

            <flux:sidebar.nav>
                <flux:sidebar.item icon="information-circle" href="#">{{ __('navigation.help') }}</flux:sidebar.item>
                @auth
                <flux:sidebar.item icon="user-circle" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.profile') }}</flux:sidebar.item>
                <flux:sidebar.item icon="cog-6-tooth" href="{{ route('account.settings', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.account_settings') }}</flux:sidebar.item>
                <flux:sidebar.item icon="shield-check" href="{{ route('account.privacy', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.privacy_settings') }}</flux:sidebar.item>
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
            <a href="{{ route('home', ['locale' => app()->getLocale()]) }}" wire:navigate class="flex min-w-0 items-center gap-2 lg:hidden">
                <x-app.brand-mark size="sm" />
                <span class="hidden truncate text-sm font-medium text-zinc-800 dark:text-white min-[380px]:block">rent2gether</span>
            </a>

            <flux:breadcrumbs class="hidden lg:flex">
                <flux:breadcrumbs.item href="{{ route('home', ['locale' => app()->getLocale()]) }}" wire:navigate>rent2gether</flux:breadcrumbs.item>
                <flux:breadcrumbs.item>{{ $title ?? __('navigation.home') }}</flux:breadcrumbs.item>
            </flux:breadcrumbs>

            <flux:spacer />

            <livewire:account.mode-switcher />
            <x-app.locale-switcher />
            <x-app.appearance-menu />

            @auth
            <livewire:notifications.notification-bell />

            <flux:dropdown align="end">
                <flux:profile :initials="Str::substr(auth()->user()->name, 0, 2)" circle />

                <flux:menu>
                    <flux:menu.item icon="user-circle" href="{{ route('profile.edit', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.profile') }}</flux:menu.item>
                    <flux:menu.item icon="sparkles" href="{{ route('profile.setup', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.profile_setup') }}</flux:menu.item>
                    <flux:menu.item icon="adjustments-horizontal" href="{{ route('profile.preferences.edit', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.guest_preferences') }}</flux:menu.item>
                    <flux:menu.item icon="cog-6-tooth" href="{{ route('account.settings', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.account_settings') }}</flux:menu.item>
                    <flux:menu.item icon="shield-check" href="{{ route('account.privacy', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.privacy_settings') }}</flux:menu.item>
                    <flux:menu.item icon="shield-check" href="{{ route('account.security', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.security_settings') }}</flux:menu.item>
                    <flux:menu.item icon="bell" href="{{ route('notifications.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.notifications') }}</flux:menu.item>
                    <flux:menu.item icon="calendar-days" href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.trips') }}</flux:menu.item>
                    <flux:menu.item icon="heart" href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.favorites') }}</flux:menu.item>
                    <flux:menu.item icon="chat-bubble-left-right" href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.messages') }}</flux:menu.item>
                    @if(auth()->user()->is_host)
                    <flux:menu.separator />
                    <flux:menu.item icon="squares-2x2" href="{{ route('host.dashboard', ['locale' => app()->getLocale()]) }}" wire:navigate>{{ __('navigation.host_dashboard') }}</flux:menu.item>
                    @endif
                    <flux:menu.separator />
                    <livewire:auth.logout-button />
                </flux:menu>
            </flux:dropdown>
            @else
            <flux:button size="sm" href="{{ route('auth.login') }}" wire:navigate>{{ __('navigation.login') }}</flux:button>
            @endauth
        </flux:header>

        <flux:main container class="!px-4 !py-4 !pb-24 sm:!px-5 sm:!py-5 lg:!px-8 lg:!py-8">
            <flux:callout wire:offline class="mb-4" variant="warning" icon="exclamation-triangle" :text="__('navigation.offline_banner')" />

            {{ $slot }}
        </flux:main>

        <x-app.mobile-nav />

        <flux:toast />

        @livewireScripts
        @fluxScripts
    </body>
</html>
