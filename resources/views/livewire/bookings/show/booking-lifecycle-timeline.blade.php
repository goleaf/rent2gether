<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('bookings.cards.lifecycle') }}</flux:heading>

    <div class="space-y-2">
        @forelse ($events as $event)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ __('bookings.lifecycle_events.'.$event->event_key) }}</flux:text>
                <flux:text size="xs" class="text-zinc-500">{{ $event->occurred_at?->translatedFormat('d M, H:i') }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_lifecycle_events') }}</flux:text>
        @endforelse
    </div>
</flux:card>
