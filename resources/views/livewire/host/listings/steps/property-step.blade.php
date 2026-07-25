<div class="space-y-4">
    <flux:card class="space-y-4">
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="tag" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.name') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="title" maxlength="120" icon="tag" />
                <flux:error name="title" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.type') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="type">
                    @foreach($propertyTypeOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="type" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="map-pin" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.address') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="address" maxlength="180" icon="map-pin" />
                <flux:error name="address" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="building-office-2" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.city') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="city" maxlength="120" icon="building-office-2" />
                <flux:error name="city" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="map" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.district') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="district" maxlength="120" icon="map" />
                <flux:error name="district" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="adjustments-horizontal" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.status') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="status">
                    @foreach($statusOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="status" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="rectangle-group" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.rooms_count') }}</span>
                    </span>
                </flux:label>
                <flux:input type="number" min="1" max="50" wire:model.blur="roomsCount" icon="rectangle-group" />
                <flux:error name="roomsCount" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('listing_wizard.property.bathrooms_count') }}</span>
                    </span>
                </flux:label>
                <flux:input type="number" min="0" max="20" wire:model.blur="bathroomsCount" icon="sparkles" />
                <flux:error name="bathroomsCount" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="document-text" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('listing_wizard.property.description') }}</span>
                </span>
            </flux:label>
            <flux:textarea rows="4" wire:model.blur="description" maxlength="2000" />
            <flux:error name="description" />
        </flux:field>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_wizard.property.amenities') }}</flux:text>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($amenityOptions as $value => $label)
                    <flux:field variant="inline" wire:key="property-amenity-{{ $value }}">
                        <flux:checkbox wire:model.change="amenities" value="{{ $value }}" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $label }}</span>
                            </span>
                        </flux:label>
                    </flux:field>
                @endforeach
            </div>
            <flux:error name="amenities" />
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_wizard.property.rules') }}</flux:text>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($ruleOptions as $value => $label)
                    <flux:field variant="inline" wire:key="property-rule-{{ $value }}">
                        <flux:checkbox wire:model.change="rules" value="{{ $value }}" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ $label }}</span>
                            </span>
                        </flux:label>
                    </flux:field>
                @endforeach
            </div>
            <flux:error name="rules" />
        </div>

        <flux:button type="button" variant="primary" wire:click="save" wire:loading.attr="disabled" wire:target="save" icon="bookmark" class="w-full sm:w-auto">
            <span wire:loading.remove wire:target="save">{{ __('listing_wizard.save_draft') }}</span>
            <span wire:loading wire:target="save">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
    </flux:card>

    <livewire:media.manage-media
        owner-type="property"
        :owner-id="$propertyId"
        collection="gallery"
        :max-items="12"
        :key="'property-media-'.$propertyId"
    />
</div>
