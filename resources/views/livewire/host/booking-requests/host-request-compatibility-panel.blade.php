<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_requests.host_view.compatibility') }}</span>
        </span>
    </flux:heading>
    @forelse($results as $result)
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <flux:text size="sm">{{ $result['message'] }}</flux:text>
            <flux:badge icon="calendar-days">{{ __('booking_requests.compatibility_statuses.'.$result['status']) }}</flux:badge>
        </div>
    @empty
        <flux:text>{{ __('booking_requests.empty.no_compatibility_results') }}</flux:text>
    @endforelse
</flux:card>
