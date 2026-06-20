@switch($section)
    @case('budget')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_budget_min') }}</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="preferredBudgetMin" />
                <flux:error name="preferredBudgetMin" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_budget_max') }}</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="preferredBudgetMax" />
                <flux:error name="preferredBudgetMax" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_currency') }}</flux:label>
                <flux:select wire:model.change="preferredCurrency">
                    <flux:select.option value="EUR">EUR</flux:select.option>
                    <flux:select.option value="USD">USD</flux:select.option>
                </flux:select>
                <flux:error name="preferredCurrency" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_city') }}</flux:label>
                <flux:input wire:model.blur="preferredCity" />
                <flux:description>{{ __('preferences.helpers.preferred_city') }}</flux:description>
                <flux:error name="preferredCity" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>{{ __('preferences.fields.max_walking_distance_to_transport_meters') }}</flux:label>
                <flux:input type="number" min="0" inputmode="numeric" wire:model.blur="maxWalkingDistanceToTransportMeters" />
                <flux:error name="maxWalkingDistanceToTransportMeters" />
            </flux:field>
        </div>
        @break

    @case('place')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_room_type') }}</flux:label>
                <flux:select wire:model.change="preferredRoomType">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="shared">{{ __('preferences.options.room_type.shared') }}</flux:select.option>
                    <flux:select.option value="private">{{ __('preferences.options.room_type.private') }}</flux:select.option>
                    <flux:select.option value="dormitory">{{ __('preferences.options.room_type.dormitory') }}</flux:select.option>
                    <flux:select.option value="studio_room">{{ __('preferences.options.room_type.studio_room') }}</flux:select.option>
                </flux:select>
                <flux:error name="preferredRoomType" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('preferences.fields.preferred_sleeping_place_type') }}</flux:label>
                <flux:select wire:model.change="preferredSleepingPlaceType">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="single">{{ __('preferences.options.sleeping_place_type.single') }}</flux:select.option>
                    <flux:select.option value="double">{{ __('preferences.options.sleeping_place_type.double') }}</flux:select.option>
                    <flux:select.option value="bunk_bottom">{{ __('preferences.options.sleeping_place_type.bunk_bottom') }}</flux:select.option>
                    <flux:select.option value="bunk_top">{{ __('preferences.options.sleeping_place_type.bunk_top') }}</flux:select.option>
                    <flux:select.option value="capsule">{{ __('preferences.options.sleeping_place_type.capsule') }}</flux:select.option>
                </flux:select>
                <flux:error name="preferredSleepingPlaceType" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>{{ __('preferences.fields.max_people_in_room') }}</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="maxPeopleInRoom" />
                <flux:error name="maxPeopleInRoom" />
            </flux:field>

            <div class="grid gap-3 sm:col-span-2">
                <flux:checkbox wire:model.change="wantsLowerBunk" label="{{ __('preferences.fields.wants_lower_bunk') }}" />
                <flux:checkbox wire:model.change="avoidsMixedRoom" label="{{ __('preferences.fields.avoids_mixed_room') }}" />
            </div>
        </div>
        @break

    @case('comfort')
        <div class="grid gap-3 sm:grid-cols-2">
            <flux:checkbox wire:model.change="wantsWifi" label="{{ __('preferences.fields.wants_wifi') }}" />
            <flux:checkbox wire:model.change="wantsKitchen" label="{{ __('preferences.fields.wants_kitchen') }}" />
            <flux:checkbox wire:model.change="wantsWashingMachine" label="{{ __('preferences.fields.wants_washing_machine') }}" />
            <flux:checkbox wire:model.change="wantsLocker" label="{{ __('preferences.fields.wants_locker') }}" />
            <flux:checkbox wire:model.change="wantsWorkspace" label="{{ __('preferences.fields.wants_workspace') }}" />
            <flux:checkbox wire:model.change="wantsQuietHours" label="{{ __('preferences.fields.wants_quiet_hours') }}" />
            <flux:checkbox wire:model.change="needsAccessibility" label="{{ __('preferences.fields.needs_accessibility') }}" />
            <flux:field>
                <flux:label>{{ __('preferences.fields.baggage_size') }}</flux:label>
                <flux:select wire:model.change="baggageSize">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="small">{{ __('preferences.options.baggage_size.small') }}</flux:select.option>
                    <flux:select.option value="medium">{{ __('preferences.options.baggage_size.medium') }}</flux:select.option>
                    <flux:select.option value="large">{{ __('preferences.options.baggage_size.large') }}</flux:select.option>
                </flux:select>
                <flux:error name="baggageSize" />
            </flux:field>
        </div>
        @break

    @case('lifestyle')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('preferences.fields.sleep_schedule') }}</flux:label>
                <flux:select wire:model.change="sleepSchedule">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="early_bird">{{ __('preferences.options.sleep_schedule.early_bird') }}</flux:select.option>
                    <flux:select.option value="night_owl">{{ __('preferences.options.sleep_schedule.night_owl') }}</flux:select.option>
                    <flux:select.option value="flexible">{{ __('preferences.options.sleep_schedule.flexible') }}</flux:select.option>
                    <flux:select.option value="regular">{{ __('preferences.options.sleep_schedule.regular') }}</flux:select.option>
                </flux:select>
                <flux:error name="sleepSchedule" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('preferences.fields.social_level') }}</flux:label>
                <flux:select wire:model.change="socialLevel">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="quiet">{{ __('preferences.options.social_level.quiet') }}</flux:select.option>
                    <flux:select.option value="balanced">{{ __('preferences.options.social_level.balanced') }}</flux:select.option>
                    <flux:select.option value="social">{{ __('preferences.options.social_level.social') }}</flux:select.option>
                </flux:select>
                <flux:error name="socialLevel" />
            </flux:field>

            <div class="grid gap-3 sm:col-span-2">
                <flux:checkbox wire:model.change="avoidsSmoking" label="{{ __('preferences.fields.avoids_smoking') }}" />
                <flux:checkbox wire:model.change="avoidsPets" label="{{ __('preferences.fields.avoids_pets') }}" />
                <flux:checkbox wire:model.change="needsLateCheckIn" label="{{ __('preferences.fields.needs_late_check_in') }}" />
                <flux:checkbox wire:model.change="needsEarlyCheckOut" label="{{ __('preferences.fields.needs_early_check_out') }}" />
            </div>

            <flux:field class="sm:col-span-2">
                <flux:label>{{ __('preferences.fields.allergies') }}</flux:label>
                <flux:textarea rows="3" wire:model.blur="allergies" />
                <flux:error name="allergies" />
            </flux:field>
        </div>
        @break
@endswitch
