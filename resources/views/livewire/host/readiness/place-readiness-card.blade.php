<section class="space-y-3">
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="check-circle">{{ __('readiness.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('readiness.statuses.'.($check?->status ?? 'checking')) }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:badge color="zinc" icon="check-circle">{{ __('readiness.reasons.'.($check?->check_reason ?? 'manual')) }}</flux:badge>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ $check?->room?->title ?? __('cleaning.empty.room') }} · {{ $check?->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
        </flux:text>
    </flux:card>

    <flux:card class="grid grid-cols-1 gap-2">
        @foreach (['checkout_completed', 'cleaning_completed', 'inspection_completed', 'repair_completed', 'inventory_ready', 'access_ready'] as $field)
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('readiness.checks.'.$field) }}</flux:text>
                <flux:badge color="zinc" icon="check-circle">{{ ($check?->{$field} ?? false) ? __('readiness.statuses.ready') : __('readiness.statuses.not_ready') }}</flux:badge>
            </div>
        @endforeach
    </flux:card>
</section>
