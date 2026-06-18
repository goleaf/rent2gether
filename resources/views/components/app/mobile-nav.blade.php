<nav
    {{ $attributes->class([
        'fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-8px_20px_rgba(39,39,42,0.06)] backdrop-blur dark:border-white/10 dark:bg-zinc-950/95 lg:hidden',
    ]) }}
    aria-label="Primary mobile navigation"
>
    <div class="grid grid-cols-4 gap-1">
        <a href="{{ url('/'.app()->getLocale()) }}" aria-current="page" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-lg bg-accent/10 px-2 text-xs font-medium text-accent-content dark:bg-white/10 dark:text-white">
            <flux:icon.home variant="mini" class="size-5" />
            <span>{{ __('app.nav.home') }}</span>
        </a>

        <a href="{{ route('health', ['locale' => app()->getLocale()]) }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-lg px-2 text-xs font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white">
            <flux:icon.calendar variant="mini" class="size-5" />
            <span>{{ __('app.nav.health') }}</span>
        </a>

        <a href="{{ route('search.index', ['locale' => app()->getLocale()]) }}" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-lg px-2 text-xs font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white">
            <flux:icon.building-office-2 variant="mini" class="size-5" />
            <span>{{ __('app.nav.search') }}</span>
        </a>

        <a href="#" class="flex min-h-12 flex-col items-center justify-center gap-1 rounded-lg px-2 text-xs font-medium text-zinc-500 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white">
            <flux:icon.users variant="mini" class="size-5" />
            <span>{{ __('app.nav.reviews') }}</span>
        </a>
    </div>
</nav>
