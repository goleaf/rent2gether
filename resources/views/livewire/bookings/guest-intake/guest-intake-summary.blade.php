<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('guest_intake.summary.guest_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.summary.guest_helper') }}</flux:text>
    </div>

    <div class="grid gap-2 text-sm sm:grid-cols-2">
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.trip_purpose') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['host_will_see']['trip_purpose'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.arrival') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['host_will_see']['planned_arrival'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.baggage') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $summary['host_will_see']['baggage'] }}</div>
        </div>
        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('guest_intake.summary.labels.documents') }}</div>
            <div class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ $summary['host_will_see']['documents_requested'] ? __('guest_intake.summary.yes') : __('guest_intake.summary.no') }}
            </div>
        </div>
    </div>

    @if(($summary['host_will_see']['warnings'] ?? []) !== [])
        <flux:callout color="amber" icon="exclamation-triangle">
            <flux:callout.heading>{{ __('guest_intake.summary.warning_title') }}</flux:callout.heading>
            <flux:callout.text>
                <ul class="list-inside list-disc space-y-1">
                    @foreach($summary['host_will_see']['warnings'] as $warning)
                        <li>{{ $warning['message'] }}</li>
                    @endforeach
                </ul>
            </flux:callout.text>
        </flux:callout>
    @endif
</flux:card>
