<section class="space-y-3">
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('inspections.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('inspections.types.'.($inspection?->inspection_type ?? 'manual')) }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:badge color="zinc" icon="user">{{ __('inspections.statuses.'.($inspection?->status ?? 'scheduled')) }}</flux:badge>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ $inspection?->room?->title ?? __('cleaning.empty.room') }} · {{ $inspection?->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
        </flux:text>
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('cleaning.sections.checklist') }}</span>
            </span>
        </flux:heading>

        @forelse (($inspection?->items ?? collect())->sortBy('sort_order') as $item)
            <div class="flex items-center justify-between gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-800">
                <flux:text size="sm">{{ __($item->label_key) }}</flux:text>
                <flux:badge color="zinc" icon="user">{{ __('cleaning.item_statuses.'.$item->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('cleaning.empty.no_checklist') }}
            </flux:text>
        @endforelse
    </flux:card>
</section>
