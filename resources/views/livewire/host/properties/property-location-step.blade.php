<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('property.steps.location.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.location.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['nearestMetro', 'nearestBusStop', 'nearestShop', 'nearestPharmacy', 'nearestHospital', 'nearestUniversity', 'nearestRailwayStation', 'nearestAirport'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['distanceToCenterMeters', 'walkMinutesToCenter', 'transportMinutesToCenter'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="number" inputmode="numeric" wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['transportConvenienceLevel', 'districtNoiseLevel', 'districtSafetyLevel', 'streetLightingLevel'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:select wire:model.change="{{ $field }}">
                        <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                        @foreach(['low', 'moderate', 'good', 'high'] as $level)
                            <flux:select.option value="{{ $level }}">{{ __('property.levels.'.$level) }}</flux:select.option>
                        @endforeach
                        @if($field === 'districtNoiseLevel')
                            <flux:select.option value="quiet">{{ __('property.levels.quiet') }}</flux:select.option>
                        @endif
                    </flux:select>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="space-y-3">
            <flux:checkbox wire:model.change="hasParkingNearby" label="{{ __('property.fields.has_parking_nearby') }}" />
            <flux:checkbox wire:model.change="hasFreeParking" label="{{ __('property.fields.has_free_parking') }}" />
            <flux:checkbox wire:model.change="hasPaidParking" label="{{ __('property.fields.has_paid_parking') }}" />
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
