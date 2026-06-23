<div>
    @if($messages)
        <flux:card class="space-y-3">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking_dates.warnings.title') }}</span>
                </span>
            </flux:heading>

            <div class="space-y-2">
                @foreach($messages as $message)
                    <flux:callout :variant="$message['blocking'] ? 'warning' : 'secondary'" icon="chat-bubble-left-right" :text="$message['message']" />
                @endforeach
            </div>
        </flux:card>
    @endif
</div>
