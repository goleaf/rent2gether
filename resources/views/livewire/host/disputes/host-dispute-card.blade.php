<article class="space-y-2 p-3">
    <flux:heading size="base">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('disputes.host_title') }}</span>
        </span>
    </flux:heading>
    <flux:badge icon="exclamation-triangle">{{ __('disputes.statuses.opened') }}</flux:badge>
</article>
