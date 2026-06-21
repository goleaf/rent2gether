<flux:card class="space-y-4">
    <flux:heading size="md">{{ __('bookings.filters.today_check_in') }}</flux:heading>

    <div class="space-y-2">
        @forelse ($bookings as $booking)
            <div class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ $booking['guest_name'] }}</flux:text>
                <flux:text size="xs" class="text-zinc-500">{{ $booking['sleeping_place'] }} · {{ $booking['dates'] }}</flux:text>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.empty_states.no_today_check_ins') }}</flux:text>
        @endforelse
    </div>
</flux:card>
