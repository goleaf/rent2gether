<flux:card class="space-y-4">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('pricing.sections.host_settings') }}</span>
        </span>
    </flux:heading>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.currency') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="currency" maxlength="3" icon="banknotes" />
            <flux:error name="currency" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.base_nightly_price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="baseNightlyPrice" icon="banknotes" />
            <flux:error name="baseNightlyPrice" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.weekday_price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="weekdayPrice" icon="banknotes" />
            <flux:error name="weekdayPrice" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.weekend_price') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="weekendPrice" icon="banknotes" />
            <flux:error name="weekendPrice" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.cleaning_fee') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="cleaningFee" icon="banknotes" />
            <flux:error name="cleaningFee" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.deposit') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="depositAmount" icon="banknotes" />
            <flux:error name="depositAmount" />
        </flux:field>
    </div>

        <flux:field variant="inline">
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('pricing.fields.deposit_required') }}</span>
            </span>
        </flux:label>
        <flux:switch wire:model.change="depositRequired" />
        <flux:error name="depositRequired" />
    </flux:field>
        <flux:field variant="inline">
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('pricing.fields.extra_guest_allowed') }}</span>
            </span>
        </flux:label>
        <flux:switch wire:model.change="extraGuestAllowed" />
        <flux:error name="extraGuestAllowed" />
    </flux:field>

    <div class="grid gap-3 sm:grid-cols-3">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.included_guests_count') }}</span>
    </span>
</flux:label>
            <flux:input type="number" wire:model.blur="includedGuestsCount" icon="users" />
            <flux:error name="includedGuestsCount" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.max_guests_count') }}</span>
    </span>
</flux:label>
            <flux:input type="number" wire:model.blur="maxGuestsCount" icon="users" />
            <flux:error name="maxGuestsCount" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('pricing.fields.extra_guest_fee') }}</span>
    </span>
</flux:label>
            <flux:input type="number" step="0.01" wire:model.blur="extraGuestFee" icon="banknotes" />
            <flux:error name="extraGuestFee" />
        </flux:field>
    </div>

    <div class="flex justify-end">
        <flux:button variant="primary" wire:click="save" wire:loading.attr="disabled" icon="check">
            {{ __('pricing.actions.save_pricing') }}
        </flux:button>
    </div>

    @if ($savedMessageKey)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.heading icon="check-circle" icon:variant="mini">{{ __($savedMessageKey) }}</flux:callout.heading>
        </flux:callout>
    @endif
</flux:card>
