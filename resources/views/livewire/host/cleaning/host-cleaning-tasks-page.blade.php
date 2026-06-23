<section class="space-y-3">
    <flux:card class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="space-y-1">
                <flux:badge color="zinc" icon="user">{{ __('cleaning.title') }}</flux:badge>
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="wrench-screwdriver" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('cleaning.sections.page') }}</span>
                    </span>
                </flux:heading>
            </div>
            <flux:badge color="zinc" icon="user">{{ __('cleaning.filters.today') }}</flux:badge>
        </div>

        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
            {{ __('cleaning.helpers.page') }}
        </flux:text>
    </flux:card>

    <div class="flex gap-2 overflow-x-auto pb-1">
        @foreach (['urgent', 'after_check_out', 'before_check_in', 'issues', 'waiting_inspection'] as $filter)
            <flux:button size="sm" variant="ghost" wire:click="$set('status', null)" wire:loading.attr="disabled" icon="funnel">
                {{ __('cleaning.filters.'.$filter) }}
            </flux:button>
        @endforeach
    </div>

    <div class="space-y-2">
        @forelse (($tasks?->items() ?? []) as $task)
            <flux:card class="space-y-2 p-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 space-y-1">
                        <flux:text class="font-medium">{{ __('cleaning.types.'.$task->cleaning_type) }}</flux:text>
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            {{ $task->room?->title ?? __('cleaning.empty.room') }} · {{ $task->sleepingPlace?->display_name ?? __('cleaning.empty.sleeping_place') }}
                        </flux:text>
                    </div>
                    <flux:badge color="zinc" icon="user">{{ __('cleaning.statuses.'.$task->status) }}</flux:badge>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:badge color="zinc" icon="heart">{{ __('cleaning.priorities.'.$task->priority) }}</flux:badge>
                    @if ($task->issues_found)
                        <flux:badge color="amber" icon="exclamation-triangle">{{ __('cleaning.fields.issues_found') }}</flux:badge>
                    @endif
                    @if ($task->inspection_required)
                        <flux:badge color="blue" icon="exclamation-triangle">{{ __('cleaning.fields.inspection_required') }}</flux:badge>
                    @endif
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('cleaning.empty.no_tasks') }}
                </flux:text>
            </flux:card>
        @endforelse
    </div>
</section>
