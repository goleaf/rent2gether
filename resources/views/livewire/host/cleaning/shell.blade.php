<section class="space-y-4">
    <flux:card class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc">{{ __('cleaning.title') }}</flux:badge>
                <flux:heading size="lg">{{ __('cleaning.sections.'.$section) }}</flux:heading>
            </div>
            <flux:badge color="zinc">{{ __('cleaning.statuses.'.($task?->status ?? 'planned')) }}</flux:badge>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.'.$section) }}
        </flux:text>
    </flux:card>

    <div class="grid grid-cols-2 gap-2">
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('cleaning.summary.today_label') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('cleaning.summary.today', ['count' => $summary['today']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('cleaning.summary.overdue_label') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('cleaning.summary.overdue', ['count' => $summary['overdue']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('cleaning.types.after_check_out') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('cleaning.summary.after_check_out', ['count' => $summary['after_check_out']]) }}</flux:text>
        </flux:card>
        <flux:card class="space-y-1 p-3">
            <flux:text size="xs" class="text-zinc-500">{{ __('cleaning.types.before_check_in') }}</flux:text>
            <flux:text size="sm" class="font-medium">{{ __('cleaning.summary.before_check_in', ['count' => $summary['before_check_in']]) }}</flux:text>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:heading size="base">{{ __('cleaning.sections.task_card') }}</flux:heading>
            <flux:badge color="zinc">{{ __('cleaning.fields.priority') }}</flux:badge>
        </div>

        @if ($task)
            <div class="space-y-2">
                <flux:text class="font-medium">{{ __('cleaning.types.'.$task->cleaning_type) }}</flux:text>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ $task->room?->title ?? __('cleaning.empty.room') }} · {{ $task->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
                </flux:text>
                <flux:text size="sm">
                    {{ optional($task->scheduled_date)->toDateString() ?? __('cleaning.empty.date') }} · {{ $task->scheduled_time ?? __('cleaning.empty.time') }}
                </flux:text>
            </div>
        @else
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('cleaning.empty.no_task_selected') }}
            </flux:text>
        @endif
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="base">{{ __('cleaning.fields.tasks') }}</flux:heading>

        @forelse (($task?->items ?? collect())->sortBy('sort_order') as $item)
            <div class="flex items-center justify-between gap-3 rounded-md border border-zinc-200 p-3 dark:border-zinc-800">
                <flux:text size="sm">{{ __($item->label_key) }}</flux:text>
                <flux:badge color="zinc">{{ __('cleaning.item_statuses.'.$item->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                {{ __('cleaning.empty.no_checklist') }}
            </flux:text>
        @endforelse
    </flux:card>

    <flux:card class="space-y-3">
        <flux:heading size="base">{{ __('cleaning.sections.flags') }}</flux:heading>
        <div class="flex flex-wrap gap-2">
            @foreach (['has_before_photos', 'has_after_photos', 'has_damage_found', 'has_forgotten_items', 'needs_repair', 'place_ready_after_cleaning'] as $flag)
                <flux:badge color="zinc">{{ __('cleaning.flags.'.$flag) }}</flux:badge>
            @endforeach
        </div>
    </flux:card>

    <div class="sticky bottom-0 -mx-4 border-t border-zinc-200 bg-white/95 p-4 dark:border-zinc-800 dark:bg-zinc-950/95">
        <div class="flex gap-2">
            <flux:button variant="ghost" class="flex-1" wire:click="startTask" wire:loading.attr="disabled">
                {{ __('cleaning.actions.start') }}
            </flux:button>
            <flux:button variant="primary" class="flex-1" wire:click="completeTask" wire:loading.attr="disabled">
                {{ __('cleaning.actions.complete') }}
            </flux:button>
        </div>
    </div>
</section>
