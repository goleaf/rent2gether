<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __($titleKey) }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __($helperKey) }}</flux:text>
    </div>

    <div class="sticky bottom-3">
        <flux:button type="button" variant="primary" class="w-full">
            {{ __($actionKey) }}
        </flux:button>
    </div>
</flux:card>
