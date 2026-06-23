<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="photo" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('sleeping_place.steps.media.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.media.helper') }}</flux:text>
    </div>

    <flux:callout icon="photo">
        <flux:callout.text>{{ __('sleeping_place.media.placeholder') }}</flux:callout.text>
    </flux:callout>
</flux:card>
