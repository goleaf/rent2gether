<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="cube" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('inventory.panels.issues') }}</span>
            </span>
        </flux:heading>
        @forelse ($issues as $issue)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.issue_types.'.$issue->issue_type) }}</flux:text>
                <flux:badge color="amber" icon="exclamation-triangle">{{ __('inventory.issue_statuses.'.$issue->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_issues') }}</flux:text>
        @endforelse
    </flux:card>
</section>
