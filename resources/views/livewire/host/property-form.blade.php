<x-ui.page>
    <section class="space-y-2">
        <flux:badge color="emerald" icon="check-circle">{{ __('host.property_wizard.eyebrow') }}</flux:badge>
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('host.property_wizard.heading') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('host.property_wizard.helper') }}</flux:text>
    </section>

    @if($wasSaved)
        <flux:callout color="green" icon="check-circle">
            <flux:callout.text>{{ __('host.property_wizard.saved_notice') }}</flux:callout.text>
        </flux:callout>
    @endif

    <flux:card class="space-y-3">
        <div class="flex items-center justify-between gap-3">
            <flux:text size="sm" class="font-medium text-zinc-700 dark:text-zinc-200">
                {{ __('host.property_wizard.progress', ['current' => $step, 'total' => 9]) }}
            </flux:text>
            @if($propertyId)
                <flux:badge size="sm" icon="clock">{{ __('statuses.property.draft') }}</flux:badge>
            @endif
        </div>

        <div class="h-2 overflow-hidden rounded-full bg-zinc-100 dark:bg-zinc-800">
            <div class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ (int) (($step / 9) * 100) }}%"></div>
        </div>

        <div class="flex gap-2 overflow-x-auto pb-1">
            @foreach($this->wizardSteps() as $wizardStep)
                <flux:button
                    type="button"
                    size="xs"
                    variant="{{ $step === $wizardStep['number'] ? 'primary' : 'outline' }}"
                    wire:click="$set('step', {{ $wizardStep['number'] }})"
                    class="shrink-0"
                    tooltip="{{ $wizardStep['title'] }}"
                    aria-current="{{ $step === $wizardStep['number'] ? 'step' : 'false' }}"
                 icon="cursor-arrow-rays">
                    {{ $wizardStep['number'] }}
                </flux:button>
            @endforeach
        </div>
    </flux:card>

    <form wire:submit="publish" class="space-y-5">
        <flux:card class="space-y-5">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('host.property_wizard.steps.'.$step.'.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                    {{ __('host.property_wizard.steps.'.$step.'.helper') }}
                </flux:text>
            </div>

            @switch($step)
                @case(1)
                    <div class="grid gap-3">
                        @foreach($this->rentalUnitTypeOptions() as $value => $label)
                            <flux:button
                                type="button"
                                variant="{{ $rentalUnitType === $value ? 'primary' : 'outline' }}"
                                wire:click="$set('rentalUnitType', '{{ $value }}')"
                                class="h-auto min-h-14 w-full justify-between whitespace-normal px-4 py-3 text-left"
                                aria-pressed="{{ $rentalUnitType === $value ? 'true' : 'false' }}"
                                icon="home-modern"
                            >
                                <span class="font-medium">{{ $label }}</span>
                                @if($rentalUnitType === $value)
                                    <flux:icon name="check-circle" class="size-5 text-emerald-600" />
                                @endif
                            </flux:button>
                        @endforeach
                    </div>
                    <flux:error name="rentalUnitType" />
                    @break

                @case(2)
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($this->propertyTypeOptions() as $value => $label)
                            <flux:button
                                type="button"
                                variant="{{ $propertyType === $value ? 'primary' : 'outline' }}"
                                wire:click="$set('propertyType', '{{ $value }}')"
                                class="h-auto min-h-14 w-full justify-between whitespace-normal px-4 py-3 text-left"
                                aria-pressed="{{ $propertyType === $value ? 'true' : 'false' }}"
                                icon="home-modern"
                            >
                                <span class="font-medium">{{ $label }}</span>
                                @if($propertyType === $value)
                                    <flux:icon name="check-circle" class="size-5 text-emerald-600" />
                                @endif
                            </flux:button>
                        @endforeach
                    </div>
                    <flux:error name="propertyType" />
                    @break

                @case(3)
                    <div class="grid gap-4">
                        @include('livewire.geo.partials.country-city-autocomplete', [
                            'autocompleteKey' => 'property',
                            'countryLabel' => __('host.property_wizard.fields.country'),
                            'countryDescription' => __('host.property_wizard.helpers.country'),
                            'countryPlaceholder' => __('host.property_wizard.placeholders.country'),
                            'cityLabel' => __('host.property_wizard.fields.city'),
                            'cityDescription' => $this->cityAutocompleteDisabled ? __('geo.helpers.city_disabled') : __('host.property_wizard.helpers.city'),
                            'cityPlaceholder' => $this->cityAutocompleteDisabled ? __('geo.placeholders.city_disabled') : __('host.property_wizard.placeholders.city'),
                        ])

                        @if($countryQuery !== '' || $cityQuery !== '')
                            <div class="flex flex-wrap gap-2">
                                @if($countryQuery !== '')
                                    <flux:badge size="sm" icon="home-modern">{{ $countryQuery }}</flux:badge>
                                @endif
                                @if($cityQuery !== '')
                                    <flux:badge size="sm" icon="home-modern">{{ $cityQuery }}</flux:badge>
                                @endif
                            </div>
                        @endif

                        <div class="grid gap-4 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.region') }}</span>
    </span>
</flux:label>
                                <flux:input wire:model.blur="regionName" icon="user" />
                                <flux:error name="regionName" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.district') }}</span>
    </span>
</flux:label>
                                <flux:input wire:model.blur="district" icon="map-pin" />
                                <flux:error name="district" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.street') }}</span>
    </span>
</flux:label>
                                <flux:input wire:model.blur="street" icon="map-pin" />
                                <flux:error name="street" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.house_number') }}</span>
    </span>
</flux:label>
                                <flux:input wire:model.blur="houseNumber" icon="pencil-square" />
                                <flux:error name="houseNumber" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.apartment_number') }}</span>
    </span>
</flux:label>
                                <flux:input wire:model.blur="apartmentNumber" icon="pencil-square" />
                                <flux:error name="apartmentNumber" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.floor') }}</span>
    </span>
</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="floor" icon="home-modern" />
                                <flux:error name="floor" />
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.total_floors') }}</span>
    </span>
</flux:label>
                                <flux:input type="number" inputmode="numeric" wire:model.blur="totalFloors" icon="home-modern" />
                                <flux:error name="totalFloors" />
                            </flux:field>
                        </div>

                        <div class="grid gap-3">
                                                        <flux:field variant="inline">
                                <flux:checkbox wire:model.change="hasElevator" />
                                <flux:label>
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ __('host.property_wizard.fields.has_elevator') }}</span>
                                    </span>
                                </flux:label>
                                <flux:error name="hasElevator" />
                            </flux:field>
                                                        <flux:field variant="inline">
                                <flux:checkbox wire:model.change="useApproximatePublicLocation" />
                                <flux:label>
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ __('host.property_wizard.fields.approximate_public_location') }}</span>
                                    </span>
                                </flux:label>
                                <flux:error name="useApproximatePublicLocation" />
                            </flux:field>
                                                        <flux:field variant="inline">
                                <flux:checkbox wire:model.change="hideExactAddressUntilBooking" />
                                <flux:label>
                                    <span class="inline-flex min-w-0 items-center gap-1.5">
                                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                        <span class="min-w-0">{{ __('host.property_wizard.fields.hide_exact_address') }}</span>
                                    </span>
                                </flux:label>
                                <flux:error name="hideExactAddressUntilBooking" />
                            </flux:field>
                        </div>
                    </div>
                    @break

                @case(4)
                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.total_area') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="decimal" step="0.1" wire:model.blur="totalArea" icon="home-modern" />
                            <flux:error name="totalArea" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.rooms_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="roomsCount" icon="home-modern" />
                            <flux:error name="roomsCount" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.bathrooms_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="bathroomsCount" icon="home-modern" />
                            <flux:error name="bathroomsCount" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.showers_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="showersCount" icon="numbered-list" />
                            <flux:error name="showersCount" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.kitchens_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="kitchensCount" icon="numbered-list" />
                            <flux:error name="kitchensCount" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.balconies_count') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="balconiesCount" icon="numbered-list" />
                            <flux:error name="balconiesCount" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.max_guests') }}</span>
    </span>
</flux:label>
                            <flux:input type="number" inputmode="numeric" wire:model.blur="maxGuests" icon="users" />
                            <flux:error name="maxGuests" />
                        </flux:field>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach(['repairState' => 'repair_state', 'noiseLevel' => 'noise_level', 'cleanlinessLevel' => 'cleanliness_level', 'safetyLevel' => 'safety_level'] as $property => $field)
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.fields.'.$field) }}</span>
    </span>
</flux:label>
                                <flux:select wire:model.change="{{ $property }}">
                                    <flux:select.option value="">{{ __('host.property_wizard.options.not_specified') }}</flux:select.option>
                                    @foreach($this->levelOptions($field) as $value => $label)
                                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                                <flux:error name="{{ $property }}" />
                            </flux:field>
                        @endforeach
                    </div>
                    @break

                @case(5)
                    <div class="grid gap-5">
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
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.title', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:input wire:model.blur="translations.{{ $locale['code'] }}.title" icon="language" />
                                    <flux:error name="translations.{{ $locale['code'] }}.title" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.summary', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="2" wire:model.blur="translations.{{ $locale['code'] }}.summary" />
                                    <flux:error name="translations.{{ $locale['code'] }}.summary" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.description', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="4" wire:model.blur="translations.{{ $locale['code'] }}.description" />
                                    <flux:error name="translations.{{ $locale['code'] }}.description" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.what_to_know', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="3" wire:model.blur="translations.{{ $locale['code'] }}.what_to_know" />
                                    <flux:error name="translations.{{ $locale['code'] }}.what_to_know" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.suitable_for', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="2" wire:model.blur="translations.{{ $locale['code'] }}.suitable_for" />
                                    <flux:error name="translations.{{ $locale['code'] }}.suitable_for" />
                                </flux:field>
                                <flux:field>
                                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('host.property_wizard.translation_fields.not_suitable_for', ['language' => $locale['name']]) }}</span>
    </span>
</flux:label>
                                    <flux:textarea rows="2" wire:model.blur="translations.{{ $locale['code'] }}.not_suitable_for" />
                                    <flux:error name="translations.{{ $locale['code'] }}.not_suitable_for" />
                                </flux:field>
                            </div>
                        @endforeach
                    </div>
                    @break

                @case(6)
                    <livewire:catalog.amenity-picker wire:model="amenityIds" context="property" />
                    <flux:error name="amenityIds" />
                    @break

                @case(7)
                    <livewire:catalog.rule-picker wire:model="ruleIds" context="property" />
                    <flux:error name="ruleIds" />
                    @break

                @case(8)
                    <div class="grid gap-4">
                        @if($propertyId)
                            <livewire:media.manage-media
                                owner-type="property"
                                :owner-id="$propertyId"
                                collection="gallery"
                                :max-items="16"
                                :wire:key="'property-media-'.$propertyId"
                            />
                        @endif

                        @foreach($this->wizardPhotoFields() as $photoField)
                            <div class="space-y-2">
                                <flux:file-upload
                                    wire:model="{{ $photoField['field'] }}"
                                    :label="__('host.property_wizard.photos.'.$photoField['slot'])"
                                    :description="__('host.property_wizard.helpers.photo')"
                                    :error="$errors->first($photoField['field'])"
                                >
                                    <flux:file-upload.dropzone
                                        :heading="__('host.property_wizard.photos.'.$photoField['slot'])"
                                        :text="__('host.property_wizard.helpers.photo')"
                                        with-progress
                                        inline
                                    />
                                </flux:file-upload>
                                <flux:text wire:loading wire:target="{{ $photoField['field'] }}" size="sm" class="text-zinc-500 dark:text-zinc-400">
                                    {{ __('media.manager.uploading') }}
                                </flux:text>

                                @if($photoField['preview'])
                                    <div class="mt-2 flex items-center gap-3 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-900">
                                        <img src="{{ $photoField['preview']['url'] }}" alt="{{ $photoField['preview']['caption'] }}" @if($photoField['preview']['saved']) loading="lazy" decoding="async" @endif class="size-16 shrink-0 rounded-md object-cover">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $photoField['preview']['label'] }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    @break

                @default
                    <div class="space-y-4">
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                            <flux:heading size="sm">
                                <span class="inline-flex min-w-0 items-center gap-2">
                                    <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('host.property_wizard.review.basics') }}</span>
                                </span>
                            </flux:heading>
                            <dl class="mt-3 grid gap-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-zinc-500">{{ __('host.property_wizard.fields.rental_unit_type') }}</dt>
                                    <dd class="text-right font-medium">{{ $rentalUnitType ? __('statuses.property_rental_unit_type.'.$rentalUnitType) : __('host.property_wizard.review.missing') }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-zinc-500">{{ __('host.property_wizard.fields.property_type') }}</dt>
                                    <dd class="text-right font-medium">{{ $propertyType ? __('statuses.property_type.'.$propertyType) : __('host.property_wizard.review.missing') }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-zinc-500">{{ __('host.property_wizard.fields.city') }}</dt>
                                    <dd class="text-right font-medium">{{ $cityQuery ?: __('host.property_wizard.review.missing') }}</dd>
                                </div>
                                @foreach($this->contentLocales() as $locale)
                                    <div class="flex justify-between gap-3">
                                        <dt class="text-zinc-500">{{ __('host.property_wizard.translation_fields.title', ['language' => $locale['name']]) }}</dt>
                                        <dd class="text-right font-medium">{{ data_get($translations, $locale['code'].'.title') ?: __('host.property_wizard.review.missing') }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>

                        <flux:callout icon="chat-bubble-left-right">
                            <flux:callout.text>{{ __('host.property_wizard.review.helper') }}</flux:callout.text>
                        </flux:callout>
                    </div>
            @endswitch
        </flux:card>

        <div class="sticky bottom-20 z-10 rounded-xl border border-zinc-200 bg-white/95 p-3 shadow-lg backdrop-blur dark:border-zinc-700 dark:bg-zinc-950/95 lg:static lg:border-0 lg:bg-transparent lg:p-0 lg:shadow-none">
            <div class="grid grid-cols-2 gap-3">
                <flux:button type="button" variant="ghost" wire:click="previousStep" :disabled="$step === 1" icon="arrow-left">
                    {{ __('host.property_wizard.actions.back') }}
                </flux:button>

                @if($step < 9)
                    <flux:button type="button" variant="primary" wire:click="nextStep" class="data-loading:opacity-70" icon="arrow-right">
                        <span wire:loading.remove wire:target="nextStep">{{ __('host.property_wizard.actions.save_and_continue') }}</span>
                        <span wire:loading wire:target="nextStep">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @else
                    <flux:button type="submit" variant="primary" class="data-loading:opacity-70" icon="eye">
                        <span wire:loading.remove wire:target="publish">{{ __('host.property_wizard.actions.review_and_save') }}</span>
                        <span wire:loading wire:target="publish">{{ __('account.actions.saving') }}</span>
                    </flux:button>
                @endif
            </div>
        </div>
    </form>
</x-ui.page>
