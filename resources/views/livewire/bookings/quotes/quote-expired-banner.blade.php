<div>
    @if($expired)
        <flux:callout variant="warning" icon="clock" :text="__('booking_quotes.messages.quote_expired')" />
    @else
        <flux:callout variant="secondary" icon="clock" :text="__('booking_quotes.messages.quote_expires_at', ['time' => $expiresAt])" />
    @endif
</div>
