<flux:card class="space-y-4">
    <div>
        <flux:heading size="lg">{{ __('room.steps.media.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('room.steps.media.helper') }}</flux:text>
    </div>

    <flux:callout icon="photo">
        <flux:callout.text>{{ __('room.media.placeholder') }}</flux:callout.text>
    </flux:callout>
</flux:card>
