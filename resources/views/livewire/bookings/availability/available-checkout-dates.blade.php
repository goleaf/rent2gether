<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('availability.checkout_dates.title') }}</flux:heading>
        <flux:text size="sm">{{ __('availability.checkout_dates.helper') }}</flux:text>
    </div>

    <div class="flex flex-wrap gap-2">
        @forelse($this->dates as $date)
            <flux:badge>
                {{ __('availability.checkout_dates.option', ['date' => $date['check_out'], 'nights' => $date['nights']]) }}
            </flux:badge>
        @empty
            <flux:text size="sm">{{ __('availability.empty.available_checkouts') }}</flux:text>
        @endforelse
    </div>
</flux:card>
