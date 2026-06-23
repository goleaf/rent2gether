<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('availability.range_summary.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm">{{ __('availability.range_summary.helper') }}</flux:text>
        </div>

        <flux:badge icon="calendar-days">
            {{ __('availability.statuses.'.$this->summary['status']) }}
        </flux:badge>
    </div>

    @if($this->summary['available'])
        <flux:text size="sm">{{ __('availability.messages.ready_text') }}</flux:text>
    @else
        <livewire:bookings.availability.availability-warnings :reasons="$this->summary['reasons']" />
    @endif
</flux:card>
