<nav
    {{ $attributes->class([
        'fixed inset-x-0 bottom-0 z-30 border-t border-zinc-200 bg-white/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-8px_20px_rgba(39,39,42,0.06)] backdrop-blur dark:border-white/10 dark:bg-zinc-950/95 lg:hidden',
    ]) }}
    aria-label="{{ $isHostMode ? __('navigation.host_mobile') : __('navigation.primary_mobile') }}"
>
    <div @class(['grid gap-1', $gridColumns])>
        @foreach($items as $item)
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
