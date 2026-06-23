<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __($titleKey) }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __($helperKey) }}</flux:text>
    </div>

    <div class="sticky bottom-3">
        <flux:button type="button" variant="primary" class="w-full" icon="check">
            {{ __($actionKey) }}
        </flux:button>
    </div>
</flux:card>
