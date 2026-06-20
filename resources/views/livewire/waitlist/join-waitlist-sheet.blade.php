<div class="space-y-4 rounded-xl border border-zinc-200 bg-white p-4 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
    <div class="space-y-1">
        <flux:heading size="lg">{{ __('waitlist.join') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('waitlist.helper') }}</flux:text>
    </div>

    @if($joined)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ __('waitlist.messages.joined') }}</flux:callout.text>
        </flux:callout>
    @endif

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('waitlist.check_in') }}</flux:label>
            <flux:input type="date" min="{{ now()->toDateString() }}" wire:model.change="desiredCheckIn" />
            <flux:error name="desiredCheckIn" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('waitlist.check_out') }}</flux:label>
            <flux:input type="date" min="{{ $desiredCheckIn ?: now()->toDateString() }}" wire:model.change="desiredCheckOut" />
            <flux:error name="desiredCheckOut" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('waitlist.guests_count') }}</flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.change="guestsCount" />
            <flux:error name="guestsCount" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('waitlist.max_price') }}</flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxPricePerNight" />
            <flux:error name="maxPricePerNight" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('waitlist.max_total_price') }}</flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxTotalPrice" />
            <flux:error name="maxTotalPrice" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('waitlist.max_deposit') }}</flux:label>
            <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="maxDeposit" />
            <flux:error name="maxDeposit" />
        </flux:field>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:checkbox wire:model.change="flexibleDates" label="{{ __('waitlist.flexible_dates') }}" />
        <flux:checkbox wire:model.change="readyToBookImmediately" label="{{ __('waitlist.ready_to_book') }}" />
        <flux:checkbox wire:model.change="autoSendRequest" label="{{ __('waitlist.auto_send_request') }}" />
        <flux:checkbox wire:model.change="notifyAvailable" label="{{ __('waitlist.notify_available') }}" />
        <flux:checkbox wire:model.change="notifyPriceDrop" label="{{ __('waitlist.notify_price_drop') }}" />
    </div>

    <flux:field>
        <flux:label>{{ __('waitlist.guest_message') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="guestMessage" />
        <flux:error name="guestMessage" />
    </flux:field>

    <div class="sticky bottom-20 -mx-4 border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-800 dark:bg-zinc-950 sm:static sm:mx-0 sm:border-0 sm:bg-transparent sm:px-0 sm:py-0 sm:dark:bg-transparent">
        <flux:button type="button" variant="primary" class="w-full sm:w-auto" wire:click="join" wire:loading.attr="disabled" wire:target="join">
            {{ __('waitlist.join') }}
        </flux:button>
    </div>
</div>
