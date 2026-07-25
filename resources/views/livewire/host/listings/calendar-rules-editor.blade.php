<flux:card class="space-y-4">
    <div class="space-y-1">
        <flux:heading size="md">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('listing_calendar.rules_title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('listing_calendar.rules_helper') }}</flux:text>
    </div>

    @if($places->isNotEmpty())
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.sleeping_place') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="sleepingPlaceId">
                    @foreach($places as $place)
                        <flux:select.option value="{{ $place->id }}">
                            {{ $place->display_name ?: __('listing_wizard.defaults.sleeping_place_name', ['number' => $place->place_number]) }}
                        </flux:select.option>
                    @endforeach
                </flux:select>
                <flux:error name="sleepingPlaceId" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.default_price') }}</span>
    </span>
</flux:label>
                <flux:input type="number" step="0.01" wire:model.blur="defaultPrice" icon="banknotes" />
                <flux:error name="defaultPrice" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.min_nights') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="1" wire:model.blur="minNights" icon="numbered-list" />
                <flux:error name="minNights" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.max_nights') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="1" wire:model.blur="maxNights" icon="numbered-list" />
                <flux:error name="maxNights" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.cleaning_gap_hours') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" wire:model.blur="cleaningGapHours" icon="numbered-list" />
                <flux:error name="cleaningGapHours" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.cleaning_gap_days') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" wire:model.blur="cleaningGapDays" icon="numbered-list" />
                <flux:error name="cleaningGapDays" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.check_in_time_from') }}</span>
    </span>
</flux:label>
                <flux:input type="time" wire:model.change="checkInTimeFrom" icon="calendar-days" />
                <flux:error name="checkInTimeFrom" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('listing_calendar.fields.check_out_time_until') }}</span>
    </span>
</flux:label>
                <flux:input type="time" wire:model.change="checkOutTimeUntil" icon="calendar-days" />
                <flux:error name="checkOutTimeUntil" />
            </flux:field>
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_calendar.fields.check_in_days') }}</flux:text>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                @foreach($weekdays as $day)
                    <flux:field variant="inline">
                        <flux:checkbox wire:model.change="checkInDays" value="{{ $day }}" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_calendar.weekdays.'.$day) }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="checkInDays" />
                    </flux:field>
                @endforeach
            </div>
            <flux:error name="checkInDays" />
        </div>

        <div class="space-y-2">
            <flux:text size="sm" class="font-medium">{{ __('listing_calendar.fields.check_out_days') }}</flux:text>
            <div class="grid grid-cols-4 gap-2 sm:grid-cols-7">
                @foreach($weekdays as $day)
                    <flux:field variant="inline">
                        <flux:checkbox wire:model.change="checkOutDays" value="{{ $day }}" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('listing_calendar.weekdays.'.$day) }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="checkOutDays" />
                    </flux:field>
                @endforeach
            </div>
            <flux:error name="checkOutDays" />
        </div>

        <flux:button type="button" variant="primary" wire:click="saveSettings" wire:loading.attr="disabled" wire:target="saveSettings" icon="calendar-days">
            <span wire:loading.remove wire:target="saveSettings">{{ __('listing_calendar.actions.save_settings') }}</span>
            <span wire:loading wire:target="saveSettings">{{ __('listing_wizard.actions.saving') }}</span>
        </flux:button>
    @else
        <div class="rounded-lg border border-dashed border-zinc-200 px-3 py-3 text-sm text-zinc-600 dark:border-zinc-700 dark:text-zinc-300">
            {{ __('listing_calendar.empty_places') }}
        </div>
    @endif
</flux:card>
