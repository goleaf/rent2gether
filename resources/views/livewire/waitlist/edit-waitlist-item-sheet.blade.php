<div class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
    <flux:heading size="lg">{{ __('waitlist.edit') }}</flux:heading>

    <flux:field>
        <flux:label>{{ __('waitlist.max_total_price') }}</flux:label>
        <flux:input type="number" min="0" step="0.01" wire:model.blur="maxTotalPrice" />
        <flux:error name="maxTotalPrice" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('waitlist.guest_message') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="guestMessage" />
        <flux:error name="guestMessage" />
    </flux:field>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save">
        {{ __('waitlist.actions.save') }}
    </flux:button>
</div>
