<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('availability.range_summary.title') }}</flux:heading>
            <flux:text size="sm">{{ __('availability.range_summary.helper') }}</flux:text>
        </div>

        <flux:badge>
            {{ __('availability.statuses.'.$this->summary['status']) }}
        </flux:badge>
    </div>

    @if($this->summary['available'])
        <flux:text size="sm">{{ __('availability.messages.ready_text') }}</flux:text>
    @else
        <livewire:bookings.availability.availability-warnings :reasons="$this->summary['reasons']" />
    @endif
</flux:card>
