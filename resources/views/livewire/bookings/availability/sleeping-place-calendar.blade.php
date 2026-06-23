<div class="space-y-4">
    <flux:card class="space-y-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('availability.calendar.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm">{{ __('availability.calendar.helper') }}</flux:text>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
            @forelse($this->calendar['days'] as $day)
                <flux:button
                    type="button"
                    wire:click="selectCheckIn('{{ $day['date'] }}')"
                    class="rounded-lg border border-zinc-200 px-3 py-2 text-left text-sm transition hover:border-zinc-400 dark:border-zinc-700"
                 icon="key">
                    <span class="block font-medium">{{ $day['date'] }}</span>
                    <span class="mt-1 block text-xs text-zinc-600 dark:text-zinc-400">
                        {{ __('availability.statuses.'.$day['public_status']) }}
                    </span>
                </flux:button>
            @empty
                <flux:text size="sm">{{ __('availability.empty.calendar_days') }}</flux:text>
            @endforelse
        </div>
    </flux:card>

    @if($selectedCheckIn)
        <livewire:bookings.availability.available-checkout-dates :sleeping-place-id="$sleepingPlaceId" :check-in="$selectedCheckIn" />
        <livewire:bookings.availability.nearest-available-dates :sleeping-place-id="$sleepingPlaceId" :preferred-check-in="$selectedCheckIn" />
    @endif
</div>
