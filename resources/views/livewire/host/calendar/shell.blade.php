<section class="space-y-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('host_calendar.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host_calendar.sections.'.$section) }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:button variant="ghost" size="sm" wire:loading.attr="disabled" icon="funnel">
                {{ __('host_calendar.actions.filters') }}
            </flux:button>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('host_calendar.helpers.'.$section) }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('host_calendar.event_types.check_in') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('host_calendar.summary.check_ins_today', ['count' => 0]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('host_calendar.event_types.check_out') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('host_calendar.summary.check_outs_today', ['count' => 0]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('host_calendar.event_types.cleaning') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('host_calendar.summary.cleanings_today', ['count' => 0]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('host_calendar.views.occupancy') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('host_calendar.summary.occupied_places', ['occupied' => 0, 'total' => 0]) }}</flux:text>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_calendar.views.property') }}</span>
            </span>
        </flux:heading>
        <div class="flex flex-wrap gap-2">
            @foreach (['property', 'room', 'sleeping_place', 'check_ins', 'check_outs', 'cleaning', 'repairs', 'payouts', 'prices', 'occupancy'] as $view)
                <flux:badge color="zinc" icon="user">{{ __('host_calendar.views.'.$view) }}</flux:badge>
            @endforeach
        </div>
    </flux:card>

    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host_calendar.actions.title') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach (['open_booking', 'message_guest', 'mark_checked_in', 'create_cleaning', 'change_price', 'create_repair'] as $action)
                <flux:button variant="ghost" class="justify-start" wire:loading.attr="disabled" icon="calendar-days">
                    {{ __('host_calendar.actions.'.$action) }}
                </flux:button>
            @endforeach
        </div>
    </flux:card>

    <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white/95 p-4 dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex gap-2">
            <flux:button variant="ghost" class="flex-1" wire:loading.attr="disabled" icon="calendar-days">
                {{ __('host_calendar.actions.add_note') }}
            </flux:button>
            <flux:button variant="primary" class="flex-1" wire:loading.attr="disabled" icon="calendar-days">
                {{ __('host_calendar.actions.open_day') }}
            </flux:button>
        </div>
    </div>
</section>
