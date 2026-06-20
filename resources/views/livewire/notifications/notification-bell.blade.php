<a
    href="{{ route('notifications.index', ['locale' => app()->getLocale()]) }}"
    wire:navigate
    class="relative inline-flex size-10 items-center justify-center rounded-lg text-zinc-600 transition hover:bg-zinc-100 hover:text-zinc-950 data-loading:opacity-70 dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white"
    aria-label="{{ __('notifications.bell.label') }}"
>
    <flux:icon name="bell" class="size-5" />

    @if($this->unreadCount > 0)
        <span class="absolute right-1.5 top-1.5 min-w-4 rounded-full bg-rose-600 px-1 text-center text-[0.625rem] font-semibold leading-4 text-white">
            {{ $this->unreadCount > 9 ? '9+' : $this->unreadCount }}
        </span>
        <span class="sr-only">
            {{ trans_choice('notifications.bell.unread_count', $this->unreadCount, ['count' => $this->unreadCount]) }}
        </span>
    @endif
</a>
