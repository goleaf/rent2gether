<div class="space-y-5">
    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.place') }}</span>
            </span>
        </flux:heading>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.district') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="district" placeholder="{{ __('search.placeholders.district') }}" icon="map-pin" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.property_type') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="propertyType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->propertyTypeOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.room_type') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="roomType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->roomTypeOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.sleeping_place_type') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="sleepingPlaceType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->sleepingPlaceTypeOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.room_gender_policy') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="roomGenderPolicy">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->genderOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.neighbors') }}</span>
            </span>
        </flux:heading>

        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('search.neighbor_notice') }}</flux:text>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.neighbor_roommates_max') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="neighborRoommatesMax" type="number" inputmode="numeric" min="0" max="1000" icon="users" />
                <flux:error name="neighborRoommatesMax" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.property_residents_max') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="propertyResidentsMax" type="number" inputmode="numeric" min="0" max="1000" icon="home-modern" />
                <flux:error name="propertyResidentsMax" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.neighbor_age_range') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="neighborAgeRange">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->neighborAgeRangeOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="neighborAgeRange" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.neighbor_lifestyle') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="neighborLifestyle">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->neighborLifestyleOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="neighborLifestyle" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="language" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.neighbor_language') }}</span>
                    </span>
                </flux:label>
                <flux:select wire:model.change="neighborLanguage">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->neighborLanguageOptions as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="neighborLanguage" />
            </flux:field>

            <flux:field>
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.fields.neighbor_min_rating') }}</span>
                    </span>
                </flux:label>
                <flux:input wire:model.blur="neighborMinRating" type="number" inputmode="decimal" min="0" max="5" step="0.1" icon="star" />
                <flux:error name="neighborMinRating" />
            </flux:field>
        </div>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->neighborFilterOptions as $neighborFilter)
                <flux:field variant="inline" wire:key="neighbor-filter-{{ $neighborFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $neighborFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $neighborFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $neighborFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $neighborFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('compatibility.filter.title') }}</span>
            </span>
        </flux:heading>

        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('compatibility.filter.helper') }}</flux:text>

        <flux:field>
            <flux:label>
                <span class="inline-flex min-w-0 items-center gap-1.5">
                    <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('compatibility.filter.minimum_fit') }}</span>
                </span>
            </flux:label>
            <flux:select wire:model.change="minimumCompatibilityFit">
                @foreach($this->compatibilityFitOptions() as $value => $label)
                    <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="minimumCompatibilityFit" />
        </flux:field>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            <flux:field variant="inline">
                <flux:checkbox wire:model.change="hideNotSuitableCompatibility" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.filter.hide_not_suitable') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hideNotSuitableCompatibility" />
            </flux:field>

            <flux:field variant="inline">
                <flux:checkbox wire:model.change="showCompatibilityWarnings" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('compatibility.filter.show_warnings') }}</span>
                    </span>
                </flux:label>
                <flux:error name="showCompatibilityWarnings" />
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="map-pin" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.location') }}</span>
            </span>
        </flux:heading>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->locationFilterOptions() as $locationFilter)
                <flux:field variant="inline" wire:key="location-filter-{{ $locationFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $locationFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $locationFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $locationFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $locationFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.condition') }}</span>
            </span>
        </flux:heading>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->conditionFilterOptions() as $conditionFilter)
                <flux:field variant="inline" wire:key="condition-filter-{{ $conditionFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $conditionFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $conditionFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $conditionFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $conditionFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.access') }}</span>
            </span>
        </flux:heading>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->accessFilterOptions() as $accessFilter)
                <flux:field variant="inline" wire:key="access-filter-{{ $accessFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $accessFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $accessFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $accessFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $accessFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.safety') }}</span>
            </span>
        </flux:heading>

        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('search.safety_notice') }}</flux:text>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->safetyFilterOptions as $safetyFilter)
                <flux:field variant="inline" wire:key="safety-filter-{{ $safetyFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $safetyFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $safetyFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $safetyFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $safetyFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="users" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.room') }}</span>
            </span>
        </flux:heading>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->roomFilterOptions as $roomFilter)
                <flux:field variant="inline" wire:key="room-filter-{{ $roomFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $roomFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $roomFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $roomFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $roomFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.premise') }}</span>
            </span>
        </flux:heading>

        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            @foreach($this->premiseFilterOptions as $premiseFilter)
                <flux:field variant="inline" wire:key="premise-filter-{{ $premiseFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $premiseFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $premiseFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $premiseFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $premiseFilter['property'] }}" />
                </flux:field>
            @endforeach
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.price') }}</span>
            </span>
        </flux:heading>

        <div class="grid grid-cols-2 gap-3">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.price_min') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="priceMin" icon="banknotes" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.price_max') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="priceMax" icon="banknotes" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.currency') }}</span>
    </span>
</flux:label>
            <flux:select wire:model.change="currency">
                <flux:select.option value="">{{ __('search.options.any_currency') }}</flux:select.option>
                <flux:select.option value="EUR">EUR</flux:select.option>
                <flux:select.option value="USD">USD</flux:select.option>
                <flux:select.option value="RUB">RUB</flux:select.option>
            </flux:select>
        </flux:field>

        <div class="space-y-2">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="noDeposit" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.no_deposit') }}</span>
                    </span>
                </flux:label>
                <flux:error name="noDeposit" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="freeCancellation" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.free_cancellation') }}</span>
                    </span>
                </flux:label>
                <flux:error name="freeCancellation" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="longStayAllowed" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.long_stay_allowed') }}</span>
                    </span>
                </flux:label>
                <flux:error name="longStayAllowed" />
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.booking') }}</span>
            </span>
        </flux:heading>
        <div class="space-y-2">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="instantBooking" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.instant_booking') }}</span>
                    </span>
                </flux:label>
                <flux:error name="instantBooking" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hostApprovalRequired" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.host_approval_required') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hostApprovalRequired" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="availableToday" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.available_today') }}</span>
                    </span>
                </flux:label>
                <flux:error name="availableToday" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="flexibleDates" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.flexible_dates') }}</span>
                    </span>
                </flux:label>
                <flux:error name="flexibleDates" />
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.comfort') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wifi" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.wifi') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wifi" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="kitchen" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.kitchen') }}</span>
                    </span>
                </flux:label>
                <flux:error name="kitchen" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="washingMachine" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.washing_machine') }}</span>
                    </span>
                </flux:label>
                <flux:error name="washingMachine" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="locker" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.locker') }}</span>
                    </span>
                </flux:label>
                <flux:error name="locker" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="beddingIncluded" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.bedding_included') }}</span>
                    </span>
                </flux:label>
                <flux:error name="beddingIncluded" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="towelIncluded" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.towel_included') }}</span>
                    </span>
                </flux:label>
                <flux:error name="towelIncluded" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="workspace" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.workspace') }}</span>
                    </span>
                </flux:label>
                <flux:error name="workspace" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="elevator" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.elevator') }}</span>
                    </span>
                </flux:label>
                <flux:error name="elevator" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="parking" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.parking') }}</span>
                    </span>
                </flux:label>
                <flux:error name="parking" />
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.room_rules') }}</span>
            </span>
        </flux:heading>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="lowerBunkOnly" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.lower_bunk_only') }}</span>
                    </span>
                </flux:label>
                <flux:error name="lowerBunkOnly" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="notUpperBunk" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.not_upper_bunk') }}</span>
                    </span>
                </flux:label>
                <flux:error name="notUpperBunk" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="lateCheckIn" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.late_check_in') }}</span>
                    </span>
                </flux:label>
                <flux:error name="lateCheckIn" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="selfCheckIn" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.self_check_in') }}</span>
                    </span>
                </flux:label>
                <flux:error name="selfCheckIn" />
            </flux:field>
            @foreach($this->ruleFilterOptions as $ruleFilter)
                <flux:field variant="inline" wire:key="rule-filter-{{ $ruleFilter['property'] }}">
                    <flux:checkbox wire:model.change="{{ $ruleFilter['property'] }}" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="{{ $ruleFilter['icon'] }}" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ $ruleFilter['label'] }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="{{ $ruleFilter['property'] }}" />
                </flux:field>
            @endforeach
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="noMixedRoom" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.no_mixed_room') }}</span>
                    </span>
                </flux:label>
                <flux:error name="noMixedRoom" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('search.fields.max_people_in_room') }}</span>
    </span>
</flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="maxPeopleInRoom" icon="users" />
        </flux:field>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="magnifying-glass" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('search.filter_groups.host') }}</span>
            </span>
        </flux:heading>
        <div class="space-y-2">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="highRating" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.high_rating') }}</span>
                    </span>
                </flux:label>
                <flux:error name="highRating" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="verifiedHost" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.verified_host') }}</span>
                    </span>
                </flux:label>
                <flux:error name="verifiedHost" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasReviews" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="star" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.has_reviews') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasReviews" />
            </flux:field>
        </div>
    </div>
</div>
