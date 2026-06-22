<section class="space-y-3">
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc">{{ __('readiness.title') }}</flux:badge>
                <flux:heading size="lg">{{ __('readiness.statuses.'.($check?->status ?? 'checking')) }}</flux:heading>
            </div>
            <flux:badge color="zinc">{{ __('readiness.reasons.'.($check?->check_reason ?? 'manual')) }}</flux:badge>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ $check?->room?->title ?? __('cleaning.empty.room') }} · {{ $check?->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
        </flux:text>
    </flux:card>

    <flux:card class="grid grid-cols-1 gap-2">
        @foreach (['checkout_completed', 'cleaning_completed', 'inspection_completed', 'repair_completed', 'inventory_ready', 'access_ready'] as $field)
            <div class="flex items-center justify-between gap-3">
                <flux:text size="sm">{{ __('readiness.checks.'.$field) }}</flux:text>
                <flux:badge color="zinc">{{ ($check?->{$field} ?? false) ? __('readiness.statuses.ready') : __('readiness.statuses.not_ready') }}</flux:badge>
            </div>
        @endforeach
    </flux:card>
</section>
