<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">{{ __('listing_calendar.rules_title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.rules_helper') }}</flux:text>
    </div>

    @if($places->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.sleeping_place') }}</flux:label>
                <flux:select wire:model.change="sleepingPlaceId">
                    @foreach($places as $place)
                        <flux:select.option value="{{ $place->id }}">
                            {{ $place->display_name ?: __('listing_wizard.defaults.sleeping_place_name', ['number' => $place->place_number]) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.default_price') }}</flux:label>
                <flux:input type="number" step="0.01" wire:model.blur="defaultPrice" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.min_nights') }}</flux:label>
                <flux:input type="number" min="1" wire:model.blur="minNights" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.max_nights') }}</flux:label>
                <flux:input type="number" min="1" wire:model.blur="maxNights" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.cleaning_gap_hours') }}</flux:label>
                <flux:input type="number" min="0" wire:model.blur="cleaningGapHours" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.cleaning_gap_days') }}</flux:label>
                <flux:input type="number" min="0" wire:model.blur="cleaningGapDays" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.check_in_time_from') }}</flux:label>
                <flux:input type="time" wire:model.change="checkInTimeFrom" />
            </flux:field>
            <flux:field>
                <flux:label>{{ __('listing_calendar.fields.check_out_time_until') }}</flux:label>
                <flux:input type="time" wire:model.change="checkOutTimeUntil" />
            </flux:field>
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_calendar.fields.check_in_days') }}</flux:text>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                @foreach($weekdays as $day)
                    <flux:checkbox wire:model.change="checkInDays" value="{{ $day }}" label="{{ __('listing_calendar.weekdays.'.$day) }}" />
                @endforeach
            </div>
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_calendar.fields.check_out_days') }}</flux:text>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                @foreach($weekdays as $day)
                    <flux:checkbox wire:model.change="checkOutDays" value="{{ $day }}" label="{{ __('listing_calendar.weekdays.'.$day) }}" />
                @endforeach
            </div>
        </div>

        <flux:button type="button" variant="primary" wire:click="saveSettings" wire:loading.attr="disabled">
            {{ __('listing_calendar.actions.save_settings') }}
        </flux:button>
    @else
        <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('listing_calendar.empty_places') }}
        </div>
    @endif
</flux:card>
