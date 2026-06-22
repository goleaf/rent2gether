<x-ui.section class="space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('disputes.host_title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('disputes.messages.dispute_opened') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary">{{ __('disputes.actions.accept_proposal') }}</flux:button>
        <flux:button type="button" variant="ghost">{{ __('disputes.actions.send_message') }}</flux:button>
    </div>
</x-ui.section>
