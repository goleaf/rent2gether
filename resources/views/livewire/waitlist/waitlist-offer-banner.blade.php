@if($offer)
    <flux:callout color="green" icon="check-circle">
        <flux:callout.heading icon="calendar-days" icon:variant="mini">{{ __('waitlist.offer_available') }}</flux:callout.heading>
        <flux:callout.text>{{ __('waitlist.offer_expires', ['time' => $offer->offer_expires_at?->format('H:i')]) }}</flux:callout.text>
    </flux:callout>
@endif
