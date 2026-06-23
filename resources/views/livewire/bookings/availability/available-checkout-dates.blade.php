<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('availability.checkout_dates.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('availability.checkout_dates.helper') }}</flux:text>
    </div>

    <div class="flex flex-wrap gap-2">
        @forelse($this->dates as $date)
            <flux:badge icon="check-circle">
                {{ __('availability.checkout_dates.option', ['date' => $date['check_out'], 'nights' => $date['nights']]) }}
            </flux:badge>
        @empty
            <flux:text size="sm">{{ __('availability.empty.available_checkouts') }}</flux:text>
        @endforelse
    </div>
</flux:card>
