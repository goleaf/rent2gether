<section>
    @if ($issues->isNotEmpty())
        <flux:card class="space-y-2">
            <flux:heading size="base">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('inventory.panels.issue_notice') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('inventory.guest.issue_notice') }}</flux:text>
        </flux:card>
    @endif
</section>
