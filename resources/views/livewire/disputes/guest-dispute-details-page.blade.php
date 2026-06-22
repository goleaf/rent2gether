<x-ui.section class="space-y-4 p-4">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('disputes.title') }}</flux:heading>
        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('disputes.messages.some_actions_frozen') }}</flux:text>
    </div>

    <div class="grid gap-2">
        <flux:button type="button" variant="primary">{{ __('disputes.actions.create_proposal') }}</flux:button>
        <flux:button type="button" variant="ghost">{{ __('disputes.actions.add_evidence') }}</flux:button>
    </div>
</x-ui.section>
