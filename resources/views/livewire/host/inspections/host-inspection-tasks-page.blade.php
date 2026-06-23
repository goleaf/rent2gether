<section class="space-y-3">
    <flux:card class="space-y-2">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inspections.title') }}</span>
            </span>
        </flux:heading>
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
                    <flux:badge color="zinc" icon="user">{{ __('inspections.statuses.'.$task->status) }}</flux:badge>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inspections.messages.empty') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
