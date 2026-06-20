<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('property.steps.condition.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.condition.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['repairState', 'cleanlinessLevel', 'smellLevel', 'humidityLevel', 'winterTemperatureLevel', 'summerTemperatureLevel', 'indoorNoiseLevel', 'lightLevel'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:select wire:model.change="{{ $field }}">
                        <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                        @foreach(['low', 'moderate', 'good', 'high', 'normal'] as $level)
                            <flux:select.option value="{{ $level }}">{{ __('property.levels.'.$level) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="space-y-3">
            <flux:checkbox wire:model.change="hasHeating" label="{{ __('property.fields.has_heating') }}" />
            <flux:checkbox wire:model.change="hasAirConditioning" label="{{ __('property.fields.has_air_conditioning') }}" />
            <flux:checkbox wire:model.change="hasHotWater" label="{{ __('property.fields.has_hot_water') }}" />
            <flux:checkbox wire:model.change="hasInsects" label="{{ __('property.fields.has_insects') }}" />
            <flux:checkbox wire:model.change="hasMold" label="{{ __('property.fields.has_mold') }}" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            @foreach(['furnitureCondition', 'plumbingCondition', 'kitchenCondition', 'floorCondition', 'wallsCondition'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input wire:model.blur="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach(['lastCleanedAt', 'lastRepairedAt', 'lastCheckedAt'] as $field)
                <flux:field>
                    <flux:label>{{ __('property.fields.'.\Illuminate\Support\Str::snake($field)) }}</flux:label>
                    <flux:input type="date" wire:model.change="{{ $field }}" />
                    <flux:error name="{{ $field }}" />
                </flux:field>
            @endforeach
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
