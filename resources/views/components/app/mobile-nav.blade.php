@php
    $navIsHostMode = $isHostMode ?? request()->routeIs('host.*');
    $navLocale = app()->getLocale();
    $navDefinitions = $navIsHostMode
        ? [
            ['route' => 'host.listings.index', 'active' => ['host.listings.*'], 'icon' => 'building-office-2', 'label' => __('navigation.host_listings')],
            ['route' => 'host.calendar', 'active' => ['host.calendar'], 'icon' => 'calendar-days', 'label' => __('navigation.host_calendar')],
            ['route' => 'host.requests.index', 'active' => ['host.requests.*'], 'icon' => 'clipboard-document-list', 'label' => __('navigation.host_requests')],
            ['route' => 'messages.index', 'active' => ['messages.*'], 'icon' => 'chat-bubble-left-right', 'label' => __('navigation.messages')],
            ['route' => 'host.profile', 'active' => ['host.profile'], 'icon' => 'user-circle', 'label' => __('navigation.profile')],
        ]
        : [
            ['route' => 'search.index', 'active' => ['search.*'], 'icon' => 'magnifying-glass', 'label' => __('navigation.search')],
            ['route' => 'saved-searches.index', 'active' => ['saved-searches.*'], 'icon' => 'bookmark', 'label' => __('navigation.saved_searches')],
            ['route' => 'trips.index', 'active' => ['trips.*', 'guest.bookings.*', 'bookings.*'], 'icon' => 'calendar-days', 'label' => __('navigation.trips')],
            ['route' => 'favorites.index', 'active' => ['favorites.*'], 'icon' => 'heart', 'label' => __('navigation.favorites')],
            ['route' => 'messages.index', 'active' => ['messages.*'], 'icon' => 'chat-bubble-left-right', 'label' => __('navigation.messages')],
            ['route' => 'profile.edit', 'active' => ['profile.*'], 'icon' => 'user-circle', 'label' => __('navigation.profile')],
        ];
    $navItems = $items ?? array_map(fn (array $item): array => [
        'href' => route($item['route'], ['locale' => $navLocale]),
        'icon' => $item['icon'],
        'label' => $item['label'],
        'active' => request()->routeIs(...$item['active']),
    ], $navDefinitions);
    $navGridColumns = $gridColumns ?? (count($navItems) === 6 ? 'grid-cols-6' : 'grid-cols-5');
@endphp

<nav
    {{ $attributes->class([
        'fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-8px_20px_rgba(39,39,42,0.06)] backdrop-blur dark:border-white/10 dark:bg-zinc-950/95 lg:hidden',
    ]) }}
    aria-label="{{ $navIsHostMode ? __('navigation.host_mobile') : __('navigation.primary_mobile') }}"
>
    <div @class(['grid gap-1', $navGridColumns])>
        @foreach($navItems as $item)
            <a
                href="{{ $item['href'] }}"
                wire:navigate
                @class([
                    'flex min-h-12 flex-col items-center justify-center gap-1 rounded-lg px-1 text-[0.68rem] font-medium data-loading:opacity-70',
                    'bg-emerald-50 text-emerald-700 dark:bg-emerald-400/10 dark:text-emerald-300' => $item['active'],
                    'text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white' => ! $item['active'],
                ])
                @if($item['active']) aria-current="page" @endif
            >
                <flux:icon name="{{ $item['icon'] }}" variant="mini" class="size-5" />
                <span class="max-w-full truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
