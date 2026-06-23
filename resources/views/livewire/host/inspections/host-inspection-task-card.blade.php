<section>
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <flux:heading size="base">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="wrench-screwdriver" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('inspections.title') }}</span>
                </span>
            </flux:heading>
            <flux:badge color="zinc" icon="user">{{ __('inspections.statuses.'.($inspection?->status ?? 'scheduled')) }}</flux:badge>
        </div>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('inspections.types.'.($inspection?->inspection_type ?? 'manual')) }}
        </flux:text>
    </flux:card>
</section>
