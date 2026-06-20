<nav class="fixed inset-x-0 bottom-0 z-40 border-t border-zinc-200 bg-white/95 px-3 py-2 backdrop-blur dark:border-zinc-800 dark:bg-zinc-950/95" aria-label="{{ __('common.navigation.mobile') }}">
    <div class="mx-auto grid max-w-xl grid-cols-5 gap-1 text-xs">
        <a wire:navigate href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" class="rounded-lg px-2 py-2 text-center text-zinc-700 dark:text-zinc-200">
            {{ __('common.navigation.search') }}
        </a>
        <a wire:navigate href="{{ route('trips.index', ['locale' => app()->getLocale()]) }}" class="rounded-lg px-2 py-2 text-center text-zinc-700 dark:text-zinc-200">
            {{ __('common.navigation.trips') }}
        </a>
        <a wire:navigate href="{{ route('favorites.index', ['locale' => app()->getLocale()]) }}" class="rounded-lg px-2 py-2 text-center text-zinc-700 dark:text-zinc-200">
            {{ __('common.navigation.favorites') }}
        </a>
        <a wire:navigate href="{{ route('messages.index', ['locale' => app()->getLocale()]) }}" class="rounded-lg px-2 py-2 text-center text-zinc-700 dark:text-zinc-200">
            {{ __('common.navigation.messages') }}
        </a>
        <a wire:navigate href="{{ route('profile.index', ['locale' => app()->getLocale()]) }}" class="rounded-lg px-2 py-2 text-center text-zinc-700 dark:text-zinc-200">
            {{ __('common.navigation.profile') }}
        </a>
    </div>
</nav>
