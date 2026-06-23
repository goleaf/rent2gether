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
                    @foreach($this->propertyTypeOptions() as $value => $label)
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
                    @foreach($this->roomTypeOptions() as $value => $label)
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
                    @foreach($this->sleepingPlaceTypeOptions() as $value => $label)
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
                    @foreach($this->genderOptions() as $value => $label)
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
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="quietHours" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.quiet_hours') }}</span>
                    </span>
                </flux:label>
                <flux:error name="quietHours" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="noSmoking" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.no_smoking') }}</span>
                    </span>
                </flux:label>
                <flux:error name="noSmoking" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="petsAllowed" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.pets_allowed') }}</span>
                    </span>
                </flux:label>
                <flux:error name="petsAllowed" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="noPets" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="scale" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('search.filters_flags.no_pets') }}</span>
                    </span>
                </flux:label>
                <flux:error name="noPets" />
            </flux:field>
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
