<section>
    <flux:card class="space-y-2">
        <flux:heading size="base">{{ __('inventory.panels.issues') }}</flux:heading>
        @forelse ($issues as $issue)
            <div class="flex items-center justify-between gap-3">
                <flux:text>{{ __('inventory.issue_types.'.$issue->issue_type) }}</flux:text>
                <flux:badge color="amber">{{ __('inventory.issue_statuses.'.$issue->status) }}</flux:badge>
            </div>
        @empty
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.empty.no_issues') }}</flux:text>
        @endforelse
    </flux:card>
</section>
