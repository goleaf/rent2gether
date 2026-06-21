<flux:card class="space-y-3">
    <flux:heading size="md">{{ __('bookings.cards.actions') }}</flux:heading>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button variant="primary" icon="chat-bubble-left-right" class="w-full">{{ __('bookings.actions.message_host') }}</flux:button>
        <flux:button variant="filled" icon="x-mark" class="w-full">{{ __('bookings.actions.cancel') }}</flux:button>
    </div>

    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('bookings.messages.actions_depend_on_status') }}</flux:text>
</flux:card>
