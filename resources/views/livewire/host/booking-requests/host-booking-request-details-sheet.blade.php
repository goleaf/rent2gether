<flux:card class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('booking_requests.host_details.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm">{{ $request->request_number }}</flux:text>
        </div>
        <livewire:bookings.requests.booking-request-status-badge :status="$request->status" :key="'status-'.$request->id.'-'.$request->status" />
    </div>

    <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
        <flux:text size="sm">{{ __('booking_requests.fields.total_amount') }}</flux:text>
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ $total }}</span>
            </span>
        </flux:heading>
    </div>

    <livewire:host.booking-requests.host-guest-profile-preview :request="$request->id" :key="'profile-'.$request->id" />
    <livewire:host.booking-requests.host-request-compatibility-panel :request="$request->id" :key="'compat-'.$request->id" />
    <livewire:host.booking-requests.host-request-warnings-panel :request="$request->id" :key="'warnings-'.$request->id" />
    <livewire:host.booking-requests.host-booking-request-response-panel :request="$request->id" :key="'responses-'.$request->id" />
    <livewire:host.booking-requests.host-alternative-place-picker :request="$request->id" :key="'alts-'.$request->id" />
</flux:card>
