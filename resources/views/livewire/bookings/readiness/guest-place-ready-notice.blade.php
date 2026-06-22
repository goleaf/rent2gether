<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('readiness.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ $status === 'ready' ? __('readiness.messages.place_ready') : __('readiness.messages.place_not_ready') }}
        </flux:text>
    </flux:card>
</section>
