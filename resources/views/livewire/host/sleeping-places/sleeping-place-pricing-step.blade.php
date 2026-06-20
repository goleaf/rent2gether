<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('sleeping_place.steps.pricing.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('sleeping_place.steps.pricing.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('sleeping_place.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['basePricePerNight', 'weeklyPrice', 'monthlyPrice', 'weekendPrice', 'holidayPrice', 'cleaningFee', 'depositAmount', 'secondGuestFee'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="number" step="0.01" inputmode="decimal" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach

            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.currency') }}</flux:label>
                <flux:input maxlength="3" wire:model.blur="currency" />
                <flux:error name="currency" />
            </flux:field>

            @foreach(['minNights', 'maxNights'] as $field)
                <flux:field>
                    <flux:label>{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="number" inputmode="numeric" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach

            <flux:field>
                <flux:label>{{ __('sleeping_place.fields.cancellation_policy') }}</flux:label>
                <flux:input wire:model.blur="cancellationPolicy" />
                <flux:error name="cancellationPolicy" />
            </flux:field>
        </div>

        <div class="grid gap-3 sm:grid-cols-2">
            @foreach(['instantBookingEnabled', 'requiresHostApproval', 'canExtend', 'earlyCheckInAllowed', 'lateCheckOutAllowed', 'secondGuestAllowed'] as $field)
                <flux:checkbox wire:model.change="{{ $field }}" label="{{ __('sleeping_place.fields.'.\Illuminate\Support\Str::snake($field)) }}" />
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('sleeping_place.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('sleeping_place.messages.saving') }}</span>
    </flux:button>
</form>
