<div>
    @if($expired)
        <flux:callout variant="warning" icon="exclamation-triangle" :text="__('booking_quotes.messages.quote_expired')" />
    @else
        <flux:callout variant="secondary" icon="information-circle" :text="__('booking_quotes.messages.quote_expires_at', ['time' => $expiresAt])" />
    @endif
</div>
