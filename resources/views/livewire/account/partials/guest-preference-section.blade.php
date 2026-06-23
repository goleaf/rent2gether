@switch($section)
    @case('budget')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_budget_min') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="preferredBudgetMin" icon="banknotes" />
                <flux:error name="preferredBudgetMin" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_budget_max') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="decimal" wire:model.blur="preferredBudgetMax" icon="banknotes" />
                <flux:error name="preferredBudgetMax" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_currency') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="preferredCurrency">
                    <flux:select.option value="EUR">EUR</flux:select.option>
                    <flux:select.option value="USD">USD</flux:select.option>
                </flux:select>
                <flux:error name="preferredCurrency" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_city') }}</span>
    </span>
</flux:label>
                <flux:input wire:model.blur="preferredCity" icon="map-pin" />
                <flux:description>{{ __('preferences.helpers.preferred_city') }}</flux:description>
                <flux:error name="preferredCity" />
            </flux:field>

            <flux:field class="sm:col-span-2">
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.max_walking_distance_to_transport_meters') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="0" inputmode="numeric" wire:model.blur="maxWalkingDistanceToTransportMeters" icon="numbered-list" />
                <flux:error name="maxWalkingDistanceToTransportMeters" />
            </flux:field>
        </div>
        @break

    @case('place')
        <div class="grid gap-4 sm:grid-cols-2">
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_room_type') }}</span>
    </span>
</flux:label>
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
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.preferred_sleeping_place_type') }}</span>
    </span>
</flux:label>
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
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.max_people_in_room') }}</span>
    </span>
</flux:label>
                <flux:input type="number" min="1" inputmode="numeric" wire:model.blur="maxPeopleInRoom" icon="users" />
                <flux:error name="maxPeopleInRoom" />
            </flux:field>

            <div class="grid gap-3 sm:col-span-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="wantsLowerBunk" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.wants_lower_bunk') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="wantsLowerBunk" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="avoidsMixedRoom" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.avoids_mixed_room') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="avoidsMixedRoom" />
                </flux:field>
            </div>
        </div>
        @break

    @case('comfort')
        <div class="grid gap-3 sm:grid-cols-2">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsWifi" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_wifi') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsWifi" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsKitchen" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_kitchen') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsKitchen" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsWashingMachine" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_washing_machine') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsWashingMachine" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsLocker" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_locker') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsLocker" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsWorkspace" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="sparkles" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_workspace') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsWorkspace" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="wantsQuietHours" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.wants_quiet_hours') }}</span>
                    </span>
                </flux:label>
                <flux:error name="wantsQuietHours" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="needsAccessibility" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('preferences.fields.needs_accessibility') }}</span>
                    </span>
                </flux:label>
                <flux:error name="needsAccessibility" />
            </flux:field>
            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.baggage_size') }}</span>
    </span>
</flux:label>
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
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="clock" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.sleep_schedule') }}</span>
    </span>
</flux:label>
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
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.social_level') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="socialLevel">
                    <flux:select.option value="">{{ __('preferences.options.any') }}</flux:select.option>
                    <flux:select.option value="quiet">{{ __('preferences.options.social_level.quiet') }}</flux:select.option>
                    <flux:select.option value="balanced">{{ __('preferences.options.social_level.balanced') }}</flux:select.option>
                    <flux:select.option value="social">{{ __('preferences.options.social_level.social') }}</flux:select.option>
                </flux:select>
                <flux:error name="socialLevel" />
            </flux:field>

            <div class="grid gap-3 sm:col-span-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="avoidsSmoking" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.avoids_smoking') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="avoidsSmoking" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="avoidsPets" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.avoids_pets') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="avoidsPets" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsLateCheckIn" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.needs_late_check_in') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsLateCheckIn" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsEarlyCheckOut" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('preferences.fields.needs_early_check_out') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsEarlyCheckOut" />
                </flux:field>
            </div>

            <flux:field class="sm:col-span-2">
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('preferences.fields.allergies') }}</span>
    </span>
</flux:label>
                <flux:textarea rows="3" wire:model.blur="allergies" />
                <flux:error name="allergies" />
            </flux:field>
        </div>
        @break
@endswitch
