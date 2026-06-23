<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('bookings.fields.guest_message') }}</span>
        </span>
    </flux:heading>

    <flux:textarea wire:model.blur="guestMessage" rows="4" />

    <flux:button variant="primary" icon="paper-airplane" wire:click="saveMessage" wire:loading.attr="disabled">
        {{ __('bookings.actions.save_message') }}
    </flux:button>
</flux:card>
