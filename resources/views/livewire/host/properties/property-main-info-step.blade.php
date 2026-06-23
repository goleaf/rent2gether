<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('property.steps.main.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.main.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        @foreach($this->contentLocales() as $locale)
            <div class="space-y-4 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $locale['name'] }}</span>
                    </span>
                </flux:heading>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.translation_fields.title', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.translation_fields.short_description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.short_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.short_description" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.translation_fields.full_description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="5" wire:model.blur="translations.{{ $locale['code'] }}.full_description" />
                    <flux:error name="translations.{{ $locale['code'] }}.full_description" />
                </flux:field>
            </div>
        @endforeach

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.property_type') }}</span>
    </span>
</flux:label>
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
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.property_subtype') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="propertySubtype" icon="home-modern" />
                <flux:error name="propertySubtype" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.district') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="district" icon="map-pin" />
                <flux:error name="district" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.street') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="street" icon="map-pin" />
                <flux:error name="street" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.house_number') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="houseNumber" icon="pencil-square" />
                <flux:error name="houseNumber" />
            </flux:field>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.apartment_number') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="apartmentNumber" icon="pencil-square" />
                <flux:error name="apartmentNumber" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.floor') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="floor" icon="home-modern" />
                <flux:error name="floor" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.total_floors') }}</span>
    </span>
</flux:label>
                <flux:input type="number" inputmode="numeric" wire:model.blur="totalFloors" icon="home-modern" />
                <flux:error name="totalFloors" />
            </flux:field>
        </div>

        <div class="space-y-3">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasElevator" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_elevator') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasElevator" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showExactAddressBeforeBooking" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.show_exact_address_before_booking') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showExactAddressBeforeBooking" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showExactAddressAfterConfirmation" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.show_exact_address_after_confirmation') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showExactAddressAfterConfirmation" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showExactAddressAfterPayment" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.show_exact_address_after_payment') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showExactAddressAfterPayment" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="showOnlyApproximateLocation" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.show_only_approximate_location') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showOnlyApproximateLocation" />
            </flux:field>
        </div>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
