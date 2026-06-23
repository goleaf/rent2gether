<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:badge color="zinc" icon="tag">{{ __('inventory.title') }}</flux:badge>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cube" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __($titleKey) }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __($messageKey) }}
        </flux:text>
    </flux:card>
</section>
