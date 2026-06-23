<section class="space-y-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('current_occupants.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('current_occupants.sections.'.$section) }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:button variant="ghost" size="sm" wire:loading.attr="disabled" icon="funnel">
                {{ __('current_occupants.actions.filters') }}
            </flux:button>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('current_occupants.helpers.'.$section) }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.current') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.current', ['count' => $summary->currentCount]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.check_ins_today') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.check_ins_today', ['count' => $summary->checkInsTodayCount]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.check_outs_today') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.check_outs_today', ['count' => $summary->checkOutsTodayCount]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('current_occupants.summary_labels.needs_attention') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('current_occupants.summary.needs_attention', ['count' => $summary->needsAttentionCount]) }}</flux:text>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('current_occupants.filters.title') }}</span>
            </span>
        </flux:heading>
        <div class="flex flex-wrap gap-2">
            @foreach (['all', 'check_ins_today', 'check_outs_today', 'leaving_soon', 'checkout_overdue', 'payment_pending', 'complaints', 'needs_extension', 'needs_checkout', 'needs_cleaning'] as $filter)
                <flux:badge color="zinc" icon="user">{{ __('current_occupants.filters.'.$filter) }}</flux:badge>
            @endforeach
        </div>
    </flux:card>

    @if ($summary->currentCount === 0)
        <flux:card class="space-y-2">
            <flux:heading size="base">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('current_occupants.empty.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('current_occupants.empty.body') }}
            </flux:text>
        </flux:card>
    @else
        <flux:card class="space-y-3">
            <flux:heading size="base">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('current_occupants.cards.title') }}</span>
                </span>
            </flux:heading>
            <div class="grid gap-2">
                @foreach (['message_guest', 'open_booking', 'mark_checked_out', 'offer_extension', 'add_note'] as $action)
                    <flux:button variant="ghost" class="justify-start" wire:loading.attr="disabled" icon="plus">
                        {{ __('current_occupants.actions.'.$action) }}
                    </flux:button>
                @endforeach
            </div>
        </flux:card>
    @endif

    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('current_occupants.actions.title') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach (['message_guest', 'mark_checked_in', 'mark_checked_out', 'create_cleaning', 'create_inspection', 'view_complaints'] as $action)
                <flux:button variant="ghost" class="justify-start" wire:loading.attr="disabled" icon="plus">
                    {{ __('current_occupants.actions.'.$action) }}
                </flux:button>
            @endforeach
        </div>
    </flux:card>

    <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white/95 p-4 dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex gap-2">
            <flux:button variant="ghost" class="flex-1" wire:loading.attr="disabled" icon="plus">
                {{ __('current_occupants.actions.add_note') }}
            </flux:button>
            <flux:button variant="primary" class="flex-1" wire:loading.attr="disabled" icon="eye">
                {{ __('current_occupants.actions.details') }}
            </flux:button>
        </div>
    </div>
</section>
