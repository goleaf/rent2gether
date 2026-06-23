<div class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-950">
    <flux:heading size="lg">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="clock" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('waitlist.edit') }}</span>
        </span>
    </flux:heading>

    <flux:field>
        <flux:label>{{ __('waitlist.max_total_price') }}</flux:label>
        <flux:input type="number" min="0" step="0.01" wire:model.blur="maxTotalPrice" icon="banknotes" />
        <flux:error name="maxTotalPrice" />
    </flux:field>

    <flux:field>
        <flux:label>{{ __('waitlist.guest_message') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="guestMessage" />
        <flux:error name="guestMessage" />
    </flux:field>

    <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save" icon="clock">
        {{ __('waitlist.actions.save') }}
    </flux:button>
</div>
