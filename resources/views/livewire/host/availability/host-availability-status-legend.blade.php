<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('calendar.legend.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm">{{ __('calendar.legend.helper') }}</flux:text>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach($this->statuses() as $status)
            <flux:badge icon="user">{{ __('availability.statuses.'.$status) }}</flux:badge>
        @endforeach
    </div>
</flux:card>
