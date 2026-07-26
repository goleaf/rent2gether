<div class="grid gap-3 sm:grid-cols-2">
    <flux:field class="sm:col-span-2">
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="tag" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.search_name') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="title" icon="tag" />
        <flux:error name="title" />
    </flux:field>

    <flux:field class="sm:col-span-2">
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="pencil-square" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.description') }}</span>
            </span>
        </flux:label>
        <flux:textarea rows="3" wire:model.blur="description" />
        <flux:error name="description" />
    </flux:field>

    <flux:field class="relative sm:col-span-2">
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="map-pin" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.city') }}</span>
            </span>
        </flux:label>
        <flux:input
            type="search"
            wire:model.live.debounce.500ms="cityQuery"
            placeholder="{{ __('saved_searches.placeholders.city') }}"
            icon="map-pin"
        />
        <flux:error name="cityQuery" />
        <flux:error name="cityId" />

        @if($this->cityOptions !== [])
            <div class="absolute z-20 mt-1 w-full overflow-hidden rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
                @foreach($this->cityOptions as $city)
                    <flux:button
                        type="button"
                        variant="ghost"
                        class="h-auto w-full justify-start whitespace-normal rounded-none px-3 py-2 text-left"
                        wire:click="selectCity({{ $city['id'] }})"
                        icon="map-pin"
                    >
                        <span class="min-w-0">
                            <span class="block font-medium text-zinc-900 dark:text-zinc-100">{{ $city['name'] }}</span>
                            @if($city['meta'] !== '')
                                <span class="block text-xs text-zinc-500">{{ $city['meta'] }}</span>
                            @endif
                        </span>
                    </flux:button>
                @endforeach
            </div>
        @elseif(strlen($cityQuery) >= 2 && ! $cityId)
            <flux:text size="sm" class="text-zinc-500">{{ __('saved_searches.city_empty') }}</flux:text>
        @endif
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="map" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.district') }}</span>
            </span>
        </flux:label>
        <flux:input wire:model.blur="district" icon="map" />
        <flux:error name="district" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.search_status') }}</span>
            </span>
        </flux:label>
        <flux:select wire:model.change="status">
            @foreach($this->statusOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="status" />
    </flux:field>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.check_in') }}</span>
            </span>
        </flux:label>
        <flux:input type="date" wire:model.change="checkInDate" icon="calendar-days" />
        <flux:error name="checkInDate" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.check_out') }}</span>
            </span>
        </flux:label>
        <flux:input type="date" wire:model.change="checkOutDate" icon="calendar-days" />
        <flux:error name="checkOutDate" />
    </flux:field>

    <div class="rounded-lg border border-zinc-200 px-3 py-3 dark:border-zinc-800 sm:col-span-2">
        <div class="text-xs text-zinc-500">{{ __('saved_searches.nights_count') }}</div>
        <div class="font-medium text-zinc-950 dark:text-zinc-50">
            {{ $this->nightsPreview === null ? __('saved_searches.no_dates') : trans_choice('saved_searches.counts.nights', $this->nightsPreview, ['count' => $this->nightsPreview]) }}
        </div>
    </div>
</div>

<div class="grid gap-3 sm:grid-cols-3">
    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.budget_min') }}</span>
            </span>
        </flux:label>
        <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="budgetMin" icon="banknotes" />
        <flux:error name="budgetMin" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.budget_max') }}</span>
            </span>
        </flux:label>
        <flux:input type="number" min="0" step="0.01" inputmode="decimal" wire:model.blur="budgetMax" icon="banknotes" />
        <flux:error name="budgetMax" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="currency-euro" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.currency') }}</span>
            </span>
        </flux:label>
        <flux:input maxlength="3" wire:model.blur="currency" icon="currency-euro" />
        <flux:error name="currency" />
    </flux:field>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.room_type') }}</span>
            </span>
        </flux:label>
        <flux:select wire:model.change="roomType">
            <flux:select.option value="">{{ __('saved_searches.any_room_type') }}</flux:select.option>
            @foreach($this->roomTypeOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="roomType" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="moon" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.sleeping_place_type') }}</span>
            </span>
        </flux:label>
        <flux:select wire:model.change="sleepingPlaceType">
            <flux:select.option value="">{{ __('saved_searches.any_sleeping_place_type') }}</flux:select.option>
            @foreach($this->sleepingPlaceTypeOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="sleepingPlaceType" />
    </flux:field>
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.required_amenities') }}</span>
            </span>
        </flux:heading>
        @foreach($this->requiredAmenityOptions as $key => $label)
            <flux:field variant="inline">
                <flux:checkbox wire:model.change="requiredAmenities.{{ $key }}" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="check-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $label }}</span>
                    </span>
                </flux:label>
                <flux:error name="requiredAmenities.{{ $key }}" />
            </flux:field>
        @endforeach
    </div>

    <div class="space-y-3">
        <flux:heading size="sm">
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="x-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.excluded_conditions') }}</span>
            </span>
        </flux:heading>
        @foreach($this->excludedConditionOptions as $key => $label)
            <flux:field variant="inline">
                <flux:checkbox wire:model.change="excludedConditions.{{ $key }}" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="x-circle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $label }}</span>
                    </span>
                </flux:label>
                <flux:error name="excludedConditions.{{ $key }}" />
            </flux:field>
        @endforeach
    </div>
</div>

<div class="grid gap-3 sm:grid-cols-2">
    <flux:field variant="inline">
        <flux:checkbox wire:model.change="onlyVerifiedHosts" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.verified_hosts_only') }}</span>
            </span>
        </flux:label>
        <flux:error name="onlyVerifiedHosts" />
    </flux:field>

    <flux:field variant="inline">
        <flux:checkbox wire:model.change="onlyInstantBooking" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.instant_booking_only') }}</span>
            </span>
        </flux:label>
        <flux:error name="onlyInstantBooking" />
    </flux:field>

    <flux:field variant="inline">
        <flux:checkbox wire:model.change="notifyNewMatches" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.notify_new_matches') }}</span>
            </span>
        </flux:label>
        <flux:error name="notifyNewMatches" />
    </flux:field>

    <flux:field variant="inline">
        <flux:checkbox wire:model.change="notifyPriceDrops" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.notify_price_drops') }}</span>
            </span>
        </flux:label>
        <flux:error name="notifyPriceDrops" />
    </flux:field>

    <flux:field variant="inline">
        <flux:checkbox wire:model.change="notifyAvailableAgain" />
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.notify_available_again') }}</span>
            </span>
        </flux:label>
        <flux:error name="notifyAvailableAgain" />
    </flux:field>

    <flux:field>
        <flux:label>
            <span class="inline-flex min-w-0 items-center gap-1.5">
                <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('saved_searches.notification_frequency') }}</span>
            </span>
        </flux:label>
        <flux:select wire:model.change="notificationFrequency">
            @foreach($this->frequencyOptions as $value => $label)
                <flux:select.option value="{{ $value }}">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
        <flux:error name="notificationFrequency" />
    </flux:field>
</div>
