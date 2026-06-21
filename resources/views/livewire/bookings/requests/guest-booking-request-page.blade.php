<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading>{{ __('booking_requests.guest_page.title') }}</flux:heading>
        <flux:text>{{ __('booking_requests.guest_page.helper') }}</flux:text>
    </div>

    <livewire:bookings.requests.booking-request-summary :request="$request->id" :key="'guest-request-summary-'.$request->id" />
    <livewire:bookings.requests.booking-request-warnings :request="$request->id" audience="guest" :key="'guest-request-warnings-'.$request->id" />
    <livewire:bookings.requests.guest-request-response-panel :request="$request->id" :key="'guest-request-response-'.$request->id" />
</div>
