<div>
    @if($messages)
        <flux:card class="space-y-3">
            <flux:heading size="sm">{{ __('booking_dates.warnings.title') }}</flux:heading>

            <div class="space-y-2">
                @foreach($messages as $message)
                    <flux:callout :variant="$message['blocking'] ? 'warning' : 'secondary'" icon="exclamation-triangle" :text="$message['message']" />
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
