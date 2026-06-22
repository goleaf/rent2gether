<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">{{ __('inspections.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('inspections.messages.guest_safe_summary') }}
        </flux:text>
    </flux:card>

    <div class="space-y-2">
        @forelse (($tasks?->items() ?? []) as $task)
            <flux:card class="space-y-2 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:text class="font-medium">{{ __('inspections.types.'.$task->inspection_type) }}</flux:text>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $task->room?->title ?? __('cleaning.empty.room') }} · {{ $task->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
                        </flux:text>
                    </div>
                    <flux:badge color="zinc">{{ __('inspections.statuses.'.$task->status) }}</flux:badge>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inspections.messages.empty') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
