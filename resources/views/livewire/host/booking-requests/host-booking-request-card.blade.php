<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">{{ $request->guest?->name }}</flux:heading>
            <flux:text size="sm">{{ $request->sleepingPlace?->display_name ?: $request->sleepingPlace?->title }}</flux:text>
        </div>
        <flux:badge>{{ $statusLabel }}</flux:badge>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.check_in_date') }}</flux:text>
            <flux:heading size="sm">{{ $request->check_in_date?->toDateString() }} · {{ $request->check_out_date?->toDateString() }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.total_amount') }}</flux:text>
            <flux:heading size="sm">{{ $total }}</flux:heading>
        </div>
    </div>

    <flux:text size="sm">{{ __('booking_requests.fields.trip_purpose') }}: {{ $purposeLabel }}</flux:text>
    <livewire:host.booking-requests.host-booking-request-details-sheet :request="$request->id" :key="'host-request-details-'.$request->id" />
</flux:card>
