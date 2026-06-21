<flux:card class="space-y-4">
    <flux:heading size="sm">{{ __('pricing.sections.host_settings') }}</flux:heading>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('pricing.fields.currency') }}</flux:label>
            <flux:input wire:model.blur="currency" maxlength="3" />
            <flux:error name="currency" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('pricing.fields.base_nightly_price') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="baseNightlyPrice" />
            <flux:error name="baseNightlyPrice" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('pricing.fields.weekday_price') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="weekdayPrice" />
            <flux:error name="weekdayPrice" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('pricing.fields.weekend_price') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="weekendPrice" />
            <flux:error name="weekendPrice" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('pricing.fields.cleaning_fee') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="cleaningFee" />
            <flux:error name="cleaningFee" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('pricing.fields.deposit') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="depositAmount" />
            <flux:error name="depositAmount" />
        </flux:field>
    </div>

    <flux:switch wire:model.change="depositRequired" label="{{ __('pricing.fields.deposit_required') }}" />
    <flux:switch wire:model.change="extraGuestAllowed" label="{{ __('pricing.fields.extra_guest_allowed') }}" />

    <div class="grid gap-3 sm:grid-cols-3">
        <flux:field>
            <flux:label>{{ __('pricing.fields.included_guests_count') }}</flux:label>
            <flux:input type="number" wire:model.blur="includedGuestsCount" />
            <flux:error name="includedGuestsCount" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.max_guests_count') }}</flux:label>
            <flux:input type="number" wire:model.blur="maxGuestsCount" />
            <flux:error name="maxGuestsCount" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('pricing.fields.extra_guest_fee') }}</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="extraGuestFee" />
            <flux:error name="extraGuestFee" />
        </flux:field>
    </div>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled">
            {{ __('pricing.actions.save_pricing') }}
        </flux:button>
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green">
            <flux:callout.heading>{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
