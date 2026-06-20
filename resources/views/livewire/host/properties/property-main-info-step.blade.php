<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('property.steps.main.title') }}</flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.main.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        @foreach($this->contentLocales() as $locale)
            <div class="space-y-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:heading size="sm">{{ $locale['name'] }}</flux:heading>
                <flux:field>
                    <flux:label>{{ __('property.translation_fields.title', ['language' => $locale['name']]) }}</flux:label>
                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" />
                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('property.translation_fields.short_description', ['language' => $locale['name']]) }}</flux:label>
                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.short_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.short_description" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('property.translation_fields.full_description', ['language' => $locale['name']]) }}</flux:label>
                    <flux:textarea rows="5" wire:model.blur="translations.{{ $locale['code'] }}.full_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.full_description" />
                </flux:field>
            </div>
        @endforeach

        <flux:field>
            <flux:label>{{ __('property.fields.property_type') }}</flux:label>
            <flux:select wire:model.change="propertyType">
                <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                @foreach(\App\Enums\PropertyType::cases() as $type)
                    <flux:select.option value="{{ $type->value }}">{{ $type->label() }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="propertyType" />
        </flux:field>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('property.fields.property_subtype') }}</flux:label>
                <flux:input wire:model.blur="propertySubtype" />
                <flux:error name="propertySubtype" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('property.fields.district') }}</flux:label>
                <flux:input wire:model.blur="district" />
                <flux:error name="district" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('property.fields.street') }}</flux:label>
                <flux:input wire:model.blur="street" />
                <flux:error name="street" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('property.fields.house_number') }}</flux:label>
                <flux:input wire:model.blur="houseNumber" />
                <flux:error name="houseNumber" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>{{ __('property.fields.apartment_number') }}</flux:label>
                <flux:input wire:model.blur="apartmentNumber" />
                <flux:error name="apartmentNumber" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('property.fields.floor') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="floor" />
                <flux:error name="floor" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('property.fields.total_floors') }}</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="totalFloors" />
                <flux:error name="totalFloors" />
            </flux:field>
        </div>

        <div class="space-y-3">
            <flux:checkbox wire:model.change="hasElevator" label="{{ __('property.fields.has_elevator') }}" />
            <flux:checkbox wire:model.change="showExactAddressBeforeBooking" label="{{ __('property.fields.show_exact_address_before_booking') }}" />
            <flux:checkbox wire:model.change="showExactAddressAfterConfirmation" label="{{ __('property.fields.show_exact_address_after_confirmation') }}" />
            <flux:checkbox wire:model.change="showExactAddressAfterPayment" label="{{ __('property.fields.show_exact_address_after_payment') }}" />
            <flux:checkbox wire:model.change="showOnlyApproximateLocation" label="{{ __('property.fields.show_only_approximate_location') }}" />
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
