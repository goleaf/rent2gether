<div class="space-y-5">
    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.place') }}</flux:heading>

        <flux:field>
            <flux:label>{{ __('search.fields.district') }}</flux:label>
            <flux:input wire:model.blur="district" placeholder="{{ __('search.placeholders.district') }}" />
        </flux:field>

        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
            <flux:field>
                <flux:label>{{ __('search.fields.property_type') }}</flux:label>
                <flux:select wire:model.change="propertyType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->propertyTypeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('search.fields.room_type') }}</flux:label>
                <flux:select wire:model.change="roomType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->roomTypeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('search.fields.sleeping_place_type') }}</flux:label>
                <flux:select wire:model.change="sleepingPlaceType">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->sleepingPlaceTypeOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>

            <flux:field>
                <flux:label>{{ __('search.fields.room_gender_policy') }}</flux:label>
                <flux:select wire:model.change="roomGenderPolicy">
                    <flux:select.option value="">{{ __('search.options.any') }}</flux:select.option>
                    @foreach($this->genderOptions() as $value => $label)
                        <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.price') }}</flux:heading>

        <div class="grid grid-cols-2 gap-3">
            <flux:field>
                <flux:label>{{ __('search.fields.price_min') }}</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="priceMin" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('search.fields.price_max') }}</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="priceMax" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>{{ __('search.fields.currency') }}</flux:label>
            <flux:select wire:model.change="currency">
                <flux:select.option value="">{{ __('search.options.any_currency') }}</flux:select.option>
                <flux:select.option value="EUR">EUR</flux:select.option>
                <flux:select.option value="USD">USD</flux:select.option>
                <flux:select.option value="RUB">RUB</flux:select.option>
            </flux:select>
        </flux:field>

        <div class="space-y-2">
            <flux:checkbox wire:model.change="noDeposit" label="{{ __('search.filters_flags.no_deposit') }}" />
            <flux:checkbox wire:model.change="freeCancellation" label="{{ __('search.filters_flags.free_cancellation') }}" />
            <flux:checkbox wire:model.change="longStayAllowed" label="{{ __('search.filters_flags.long_stay_allowed') }}" />
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.booking') }}</flux:heading>
        <div class="space-y-2">
            <flux:checkbox wire:model.change="instantBooking" label="{{ __('search.filters_flags.instant_booking') }}" />
            <flux:checkbox wire:model.change="hostApprovalRequired" label="{{ __('search.filters_flags.host_approval_required') }}" />
            <flux:checkbox wire:model.change="availableToday" label="{{ __('search.filters_flags.available_today') }}" />
            <flux:checkbox wire:model.change="flexibleDates" label="{{ __('search.filters_flags.flexible_dates') }}" />
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.comfort') }}</flux:heading>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            <flux:checkbox wire:model.change="wifi" label="{{ __('search.filters_flags.wifi') }}" />
            <flux:checkbox wire:model.change="kitchen" label="{{ __('search.filters_flags.kitchen') }}" />
            <flux:checkbox wire:model.change="washingMachine" label="{{ __('search.filters_flags.washing_machine') }}" />
            <flux:checkbox wire:model.change="locker" label="{{ __('search.filters_flags.locker') }}" />
            <flux:checkbox wire:model.change="beddingIncluded" label="{{ __('search.filters_flags.bedding_included') }}" />
            <flux:checkbox wire:model.change="towelIncluded" label="{{ __('search.filters_flags.towel_included') }}" />
            <flux:checkbox wire:model.change="workspace" label="{{ __('search.filters_flags.workspace') }}" />
            <flux:checkbox wire:model.change="elevator" label="{{ __('search.filters_flags.elevator') }}" />
            <flux:checkbox wire:model.change="parking" label="{{ __('search.filters_flags.parking') }}" />
        </div>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.room_rules') }}</flux:heading>
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-1">
            <flux:checkbox wire:model.change="lowerBunkOnly" label="{{ __('search.filters_flags.lower_bunk_only') }}" />
            <flux:checkbox wire:model.change="notUpperBunk" label="{{ __('search.filters_flags.not_upper_bunk') }}" />
            <flux:checkbox wire:model.change="lateCheckIn" label="{{ __('search.filters_flags.late_check_in') }}" />
            <flux:checkbox wire:model.change="selfCheckIn" label="{{ __('search.filters_flags.self_check_in') }}" />
            <flux:checkbox wire:model.change="quietHours" label="{{ __('search.filters_flags.quiet_hours') }}" />
            <flux:checkbox wire:model.change="noSmoking" label="{{ __('search.filters_flags.no_smoking') }}" />
            <flux:checkbox wire:model.change="petsAllowed" label="{{ __('search.filters_flags.pets_allowed') }}" />
            <flux:checkbox wire:model.change="noPets" label="{{ __('search.filters_flags.no_pets') }}" />
            <flux:checkbox wire:model.change="noMixedRoom" label="{{ __('search.filters_flags.no_mixed_room') }}" />
        </div>

        <flux:field>
            <flux:label>{{ __('search.fields.max_people_in_room') }}</flux:label>
            <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="maxPeopleInRoom" />
        </flux:field>
    </div>

    <flux:separator />

    <div class="space-y-3">
        <flux:heading size="sm">{{ __('search.filter_groups.host') }}</flux:heading>
        <div class="space-y-2">
            <flux:checkbox wire:model.change="highRating" label="{{ __('search.filters_flags.high_rating') }}" />
            <flux:checkbox wire:model.change="verifiedHost" label="{{ __('search.filters_flags.verified_host') }}" />
            <flux:checkbox wire:model.change="hasReviews" label="{{ __('search.filters_flags.has_reviews') }}" />
        </div>
    </div>
</div>
