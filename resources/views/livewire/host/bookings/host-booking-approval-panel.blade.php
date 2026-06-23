<flux:card class="space-y-4">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('bookings.host.approval') }}</span>
        </span>
    </flux:heading>

    <flux:textarea wire:model.blur="message" rows="3" placeholder="{{ __('bookings.fields.host_response') }}" />
    <flux:textarea wire:model.blur="rejectionReason" rows="3" placeholder="{{ __('bookings.fields.rejection_reason') }}" />

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button variant="primary" icon="calendar-days" wire:click="approve" wire:loading.attr="disabled">
            {{ __('bookings.actions.confirm') }}
        </flux:button>
        <flux:button variant="filled" icon="x-mark" wire:click="reject" wire:loading.attr="disabled">
            {{ __('bookings.actions.reject') }}
        </flux:button>
    </div>
</flux:card>
