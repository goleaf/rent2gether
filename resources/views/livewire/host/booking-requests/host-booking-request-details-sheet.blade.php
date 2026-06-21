<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">{{ __('booking_requests.host_details.title') }}</flux:heading>
            <flux:text size="sm">{{ $request->request_number }}</flux:text>
        </div>
        <livewire:bookings.requests.booking-request-status-badge :status="$request->status" :key="'status-'.$request->id.'-'.$request->status" />
    </div>

    <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
        <flux:text size="sm">{{ __('booking_requests.fields.total_amount') }}</flux:text>
        <flux:heading size="lg">{{ $total }}</flux:heading>
    </div>

    <livewire:host.booking-requests.host-guest-profile-preview :request="$request->id" :key="'profile-'.$request->id" />
    <livewire:host.booking-requests.host-request-compatibility-panel :request="$request->id" :key="'compat-'.$request->id" />
    <livewire:host.booking-requests.host-request-warnings-panel :request="$request->id" :key="'warnings-'.$request->id" />
    <livewire:host.booking-requests.host-booking-request-response-panel :request="$request->id" :key="'responses-'.$request->id" />
    <livewire:host.booking-requests.host-alternative-place-picker :request="$request->id" :key="'alts-'.$request->id" />
</flux:card>
