<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading>
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('booking_requests.guest_page.title') }}</span>
            </span>
        </flux:heading>
        <flux:text>{{ __('booking_requests.guest_page.helper') }}</flux:text>
    </div>

    <livewire:bookings.requests.booking-request-summary :request="$request->id" :key="'guest-request-summary-'.$request->id" />
    <livewire:bookings.requests.booking-request-warnings :request="$request->id" audience="guest" :key="'guest-request-warnings-'.$request->id" />
    <livewire:bookings.requests.guest-request-response-panel :request="$request->id" :key="'guest-request-response-'.$request->id" />
</div>
