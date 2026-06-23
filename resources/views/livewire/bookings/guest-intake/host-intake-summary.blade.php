<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('guest_intake.summary.host_title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.summary.host_helper') }}</flux:text>
    </div>

    <div class="grid gap-2 text-sm sm:grid-cols-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.trip_purpose') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['trip_purpose'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.arrival') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['planned_arrival'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.departure') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['planned_departure'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.baggage') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['baggage'] }}</div>
        </div>
    </div>

    <div class="space-y-2 text-sm">
        @if($summary['quiet_work_needs'] !== [])
            <div class="flex flex-wrap gap-1.5">
                @foreach($summary['quiet_work_needs'] as $need)
                    <flux:badge size="sm" icon="calendar-days">{{ $need }}</flux:badge>
                @endforeach
            </div>
        @endif

        @if($summary['message_to_host'])
            <div class="rounded-lg border border-zinc-200 px-3 py-2 dark:border-zinc-700">
                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.message') }}</div>
                <p class="mt-1 text-zinc-700 dark:text-zinc-300">{{ $summary['message_to_host'] }}</p>
            </div>
        @endif
    </div>

    @if($summary['warnings'] !== [] || $summary['required_confirmations'] !== [])
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('guest_intake.summary.warning_title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('guest_intake.summary.warning_helper') }}</flux:callout.text>
        </flux:callout>
    @endif
</flux:card>
