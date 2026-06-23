<x-ui.section class="space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="exclamation-triangle" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('disputes.host_title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('disputes.messages.dispute_opened') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary" icon="check">{{ __('disputes.actions.accept_proposal') }}</flux:button>
        <flux:button type="button" variant="ghost" icon="paper-airplane">{{ __('disputes.actions.send_message') }}</flux:button>
    </div>
</x-ui.section>
