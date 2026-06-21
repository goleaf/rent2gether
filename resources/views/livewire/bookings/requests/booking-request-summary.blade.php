<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">{{ $summary['number'] }}</flux:heading>
            <flux:text size="sm">{{ $summary['type'] }}</flux:text>
        </div>
        <flux:badge>{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.sleeping_place') }}</flux:text>
            <flux:heading size="sm">{{ $summary['place'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.check_in_date') }}</flux:text>
            <flux:heading size="sm">{{ $summary['dates'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.nights_count') }}</flux:text>
            <flux:heading size="sm">{{ $summary['nights_count'] }}</flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.total_amount') }}</flux:text>
            <flux:heading size="sm">{{ $summary['total'] }}</flux:heading>
        </div>
    </div>

    @if($summary['expires_at'])
        <flux:text size="sm">{{ __('booking_requests.messages.expires_at', ['time' => $summary['expires_at']]) }}</flux:text>
    @endif
</flux:card>
