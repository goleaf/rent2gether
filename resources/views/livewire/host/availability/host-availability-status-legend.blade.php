<flux:card class="space-y-3">
    <div class="space-y-1">
        <flux:heading size="sm">{{ __('calendar.legend.title') }}</flux:heading>
        <flux:text size="sm">{{ __('calendar.legend.helper') }}</flux:text>
    </div>

    <div class="flex flex-wrap gap-2">
        @foreach($this->statuses() as $status)
            <flux:badge>{{ __('availability.statuses.'.$status) }}</flux:badge>
        @endforeach
    </div>
</flux:card>
