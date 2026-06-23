<flux:card class="space-y-3">
    <div class="flex items-start justify-between gap-3">
        <div class="space-y-1">
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['number'] }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm">{{ $summary['type'] }}</flux:text>
        </div>
        <flux:badge icon="calendar-days">{{ $summary['status'] }}</flux:badge>
    </div>

    <div class="grid gap-2 sm:grid-cols-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.sleeping_place') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['place'] }}</span>
                </span>
            </flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.check_in_date') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['dates'] }}</span>
                </span>
            </flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.nights_count') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['nights_count'] }}</span>
                </span>
            </flux:heading>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ __('booking_requests.fields.total_amount') }}</flux:text>
            <flux:heading size="sm">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ $summary['total'] }}</span>
                </span>
            </flux:heading>
        </div>
    </div>

    @if($summary['expires_at'])
        <flux:text size="sm">{{ __('booking_requests.messages.expires_at', ['time' => $summary['expires_at']]) }}</flux:text>
    @endif
</flux:card>
