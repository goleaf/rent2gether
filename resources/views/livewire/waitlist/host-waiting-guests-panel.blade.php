<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('waitlist.host.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-500">{{ __('waitlist.host.helper') }}</flux:text>
        </div>
        <flux:badge color="emerald" icon="check-circle">{{ __('waitlist.host.waiting_count', ['count' => $summary['total']]) }}</flux:badge>
    </div>

    <div class="grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('waitlist.host.ready_to_book') }}</div>
            <div class="font-medium">{{ $summary['ready_to_book_count'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-zinc-500">{{ __('waitlist.host.average_budget') }}</div>
            <div class="font-medium">{{ $summary['average_max_price'] ? round($summary['average_max_price'], 2) : __('waitlist.states.no_limit') }}</div>
        </div>
    </div>

    <div class="grid gap-2">
        @forelse($summary['items'] as $item)
            <livewire:waitlist.host-waiting-guest-card :waitlist-item-id="$item->id" :key="'host-waiting-'.$item->id" />
        @empty
            <div class="rounded-lg border border-dashed border-zinc-200 p-4 text-sm text-zinc-500 dark:border-zinc-800">
                {{ __('waitlist.host.empty') }}
            </div>
        @endforelse
    </div>
</flux:card>
