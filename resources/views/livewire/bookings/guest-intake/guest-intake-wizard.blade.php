<section class="space-y-4" aria-labelledby="guest-intake-title">
    <flux:card class="space-y-4">
        <div class="space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 space-y-1">
                    <flux:badge color="emerald" icon="check-circle">{{ __('guest_intake.badge') }}</flux:badge>
                    <flux:heading id="guest-intake-title" size="lg">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.title') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.helper') }}</flux:text>
                </div>
                <flux:badge color="zinc" icon="calendar-days">{{ __('guest_intake.progress', ['current' => $step, 'total' => 6]) }}</flux:badge>
            </div>

            <flux:progress value="{{ (int) round(($step / 6) * 100) }}" />
        </div>

        @if($statusMessage)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ $statusMessage }}</flux:callout.text>
            </flux:callout>
        @endif

        <div wire:loading.delay class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-200">
            {{ __('guest_intake.messages.saving') }}
        </div>

        @if($step === 1)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.trip_purpose') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.trip_purpose') }}</flux:text>
                </div>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.trip_purpose') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="tripPurpose">
                        <flux:select.option value="">{{ __('guest_intake.placeholders.choose') }}</flux:select.option>
                        @foreach($tripPurposes as $purpose)
                            <flux:select.option value="{{ $purpose }}">{{ __('guest_intake.trip_purposes.'.$purpose) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tripPurpose" />
                </flux:field>

                @if($tripPurpose === 'other')
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.trip_purpose_other') }}</span>
    </span>
</flux:label>
                        <flux:textarea rows="2" wire:model.blur="tripPurposeOther" />
                        <flux:error name="tripPurposeOther" />
                    </flux:field>
                @endif

                @if($tripPurpose === 'medical')
                    <flux:callout color="amber" icon="exclamation-triangle">
                        <flux:callout.heading>{{ __('guest_intake.privacy.medical_title') }}</flux:callout.heading>
                        <flux:callout.text>{{ __('guest_intake.privacy.medical_helper') }}</flux:callout.text>
                    </flux:callout>
                @endif

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.trip_purpose_visibility') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="tripPurposeVisibility">
                        <flux:select.option value="safe">{{ __('guest_intake.visibility.safe') }}</flux:select.option>
                        <flux:select.option value="exact">{{ __('guest_intake.visibility.exact') }}</flux:select.option>
                    </flux:select>
                    <flux:description>{{ __('guest_intake.privacy.visibility_helper') }}</flux:description>
                    <flux:error name="tripPurposeVisibility" />
                </flux:field>
            </div>
        @elseif($step === 2)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.arrival_departure') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.arrival_departure') }}</flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.planned_arrival_date') }}</span>
    </span>
</flux:label>
                        <flux:input type="date" wire:model.change="plannedArrivalDate" icon="calendar-days" />
                        <flux:error name="plannedArrivalDate" />
                    </flux:field>
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.planned_arrival_time') }}</span>
    </span>
</flux:label>
                        <flux:input type="time" wire:model.change="plannedArrivalTime" icon="clock" />
                        <flux:error name="plannedArrivalTime" />
                    </flux:field>
                </div>

                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="arrivalTimeUnknown" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.fields.arrival_time_unknown') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="arrivalTimeUnknown" />
                </flux:field>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.planned_arrival_window') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="plannedArrivalWindow" maxlength="100" icon="clock" />
                    <flux:error name="plannedArrivalWindow" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.planned_departure_time') }}</span>
    </span>
</flux:label>
                        <flux:input type="time" wire:model.change="plannedDepartureTime" icon="clock" />
                        <flux:error name="plannedDepartureTime" />
                    </flux:field>
                    <div class="space-y-3 pt-1">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="departureTimeUnknown" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('guest_intake.fields.departure_time_unknown') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="departureTimeUnknown" />
                        </flux:field>
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="canAdjustArrivalTime" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('guest_intake.fields.can_adjust_arrival_time') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="canAdjustArrivalTime" />
                        </flux:field>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="earlyCheckInRequested" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('guest_intake.fields.early_check_in_requested') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="earlyCheckInRequested" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.requested_early_check_in_time') }}</span>
    </span>
</flux:label>
                            <flux:input type="time" wire:model.change="requestedEarlyCheckInTime" icon="calendar-days" />
                        </flux:field>
                    </div>
                    <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                                <flux:field variant="inline">
                            <flux:checkbox wire:model.change="lateCheckOutRequested" />
                            <flux:label>
                                <span class="inline-flex min-w-0 items-center gap-1.5">
                                    <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                    <span class="min-w-0">{{ __('guest_intake.fields.late_check_out_requested') }}</span>
                                </span>
                            </flux:label>
                            <flux:error name="lateCheckOutRequested" />
                        </flux:field>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.requested_late_check_out_time') }}</span>
    </span>
</flux:label>
                            <flux:input type="time" wire:model.change="requestedLateCheckOutTime" icon="calendar-days" />
                        </flux:field>
                    </div>
                </div>
            </div>
        @elseif($step === 3)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.baggage_pets_smoking') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.baggage_pets_smoking') }}</flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.baggage_level') }}</span>
    </span>
</flux:label>
                        <flux:select wire:model.change="baggageLevel">
                            <flux:select.option value="">{{ __('guest_intake.placeholders.choose') }}</flux:select.option>
                            @foreach($baggageLevels as $level)
                                <flux:select.option value="{{ $level }}">{{ __('guest_intake.baggage.'.$level) }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.baggage_count') }}</span>
    </span>
</flux:label>
                        <flux:input type="number" min="0" max="20" inputmode="numeric" wire:model.change="baggageCount" icon="numbered-list" />
                    </flux:field>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasLargeSuitcase" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.has_large_suitcase') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasLargeSuitcase" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasSpecialBaggage" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.has_special_baggage') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasSpecialBaggage" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLuggageStorageBeforeCheckin" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_luggage_storage_before_checkin') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLuggageStorageBeforeCheckin" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLuggageStorageAfterCheckout" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_luggage_storage_after_checkout') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLuggageStorageAfterCheckout" />
                    </flux:field>
                </div>

                @if($hasSpecialBaggage)
                    <flux:field>
                        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.special_baggage_type') }}</span>
    </span>
</flux:label>
                        <flux:input wire:model.blur="specialBaggageType" maxlength="100" icon="pencil-square" />
                    </flux:field>
                @endif

                <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="hasPet" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.has_pet') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="hasPet" />
                    </flux:field>
                    @if($hasPet)
                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.pet_type') }}</span>
    </span>
</flux:label>
                                <flux:select wire:model.change="petType">
                                    <flux:select.option value="">{{ __('guest_intake.placeholders.choose') }}</flux:select.option>
                                    @foreach($petTypes as $type)
                                        <flux:select.option value="{{ $type }}">{{ __('guest_intake.pet_types.'.$type) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <flux:field>
                                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.pet_size') }}</span>
    </span>
</flux:label>
                                <flux:select wire:model.change="petSize">
                                    <flux:select.option value="">{{ __('guest_intake.placeholders.choose') }}</flux:select.option>
                                    @foreach($petSizes as $size)
                                        <flux:select.option value="{{ $size }}">{{ __('guest_intake.pet_sizes.'.$size) }}</flux:select.option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        </div>
                        <flux:field>
                            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.pet_notes') }}</span>
    </span>
</flux:label>
                            <flux:textarea rows="2" wire:model.blur="petNotes" />
                        </flux:field>
                    @endif
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="smokes" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.smokes') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="smokes" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="acceptsSmokingRules" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.accepts_smoking_rules') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="acceptsSmokingRules" />
                    </flux:field>
                </div>
            </div>
        @elseif($step === 4)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.comfort_work') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.comfort_work') }}</flux:text>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsQuiet" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_quiet') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsQuiet" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsWorkspace" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_workspace') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsWorkspace" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsFastWifi" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_fast_wifi') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsFastWifi" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsPowerSocket" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_power_socket') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsPowerSocket" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsOnlineCalls" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_online_calls') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsOnlineCalls" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsLateEntry" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_late_entry') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsLateEntry" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsSelfCheckIn" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_self_check_in') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsSelfCheckIn" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.noise_sensitivity_level') }}</span>
    </span>
</flux:label>
                    <flux:select wire:model.change="noiseSensitivityLevel">
                        <flux:select.option value="">{{ __('guest_intake.placeholders.choose') }}</flux:select.option>
                        @foreach($noiseLevels as $level)
                            <flux:select.option value="{{ $level }}">{{ __('guest_intake.noise_sensitivity.'.$level) }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        @elseif($step === 5)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.documents_special_requests') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.documents_special_requests') }}</flux:text>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsRegistration" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_registration') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsRegistration" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsWorkDocuments" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_work_documents') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsWorkDocuments" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsInvoice" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_invoice') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsInvoice" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsReceipt" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_receipt') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsReceipt" />
                    </flux:field>
                                        <flux:field variant="inline">
                        <flux:checkbox wire:model.change="needsContract" />
                        <flux:label>
                            <span class="inline-flex min-w-0 items-center gap-1.5">
                                <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                                <span class="min-w-0">{{ __('guest_intake.fields.needs_contract') }}</span>
                            </span>
                        </flux:label>
                        <flux:error name="needsContract" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.company_name') }}</span>
    </span>
</flux:label>
                    <flux:input wire:model.blur="companyName" maxlength="255" icon="user" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.document_notes') }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="2" wire:model.blur="documentNotes" />
                    <flux:description>{{ __('guest_intake.privacy.document_notes_helper') }}</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.special_requests') }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="3" wire:model.blur="specialRequests" />
                </flux:field>
            </div>
        @else
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">
                        <span class="inline-flex min-w-0 items-center gap-2">
                            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.steps.host_message') }}</span>
                        </span>
                    </flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.host_message') }}</flux:text>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" size="sm" variant="ghost" icon="chat-bubble-left-right" wire:click="generateHostMessage">
                        {{ __('guest_intake.actions.generate_message') }}
                    </flux:button>
                    @foreach($templates as $template)
                        <flux:button type="button" size="sm" variant="ghost" wire:click="$set('hostMessage', @js($template))" icon="chat-bubble-left-right">
                            {{ $template }}
                        </flux:button>
                    @endforeach
                </div>

                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('guest_intake.fields.host_message') }}</span>
    </span>
</flux:label>
                    <flux:textarea rows="4" wire:model.blur="hostMessage" />
                    <flux:error name="hostMessage" />
                </flux:field>

                @if(! empty($summary['host_will_see']))
                    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ __('guest_intake.summary.host_will_see') }}</div>
                        <div class="mt-2 space-y-1 text-zinc-600 dark:text-zinc-400">
                            <div>{{ __('guest_intake.summary.labels.trip_purpose') }}: {{ $summary['host_will_see']['trip_purpose'] }}</div>
                            <div>{{ __('guest_intake.summary.labels.arrival') }}: {{ $summary['host_will_see']['planned_arrival'] }}</div>
                            <div>{{ __('guest_intake.summary.labels.baggage') }}: {{ $summary['host_will_see']['baggage'] }}</div>
                        </div>
                    </div>
                @endif

                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="rulesAccepted" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('guest_intake.fields.rules_accepted') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="rulesAccepted" />
                </flux:field>
                <flux:error name="rules_accepted" />
            </div>
        @endif

        <div class="flex items-center justify-between gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:button type="button" variant="ghost" icon="arrow-left" wire:click="previousStep" :disabled="$step === 1">
                {{ __('guest_intake.actions.previous') }}
            </flux:button>

            <div class="flex gap-2">
                <flux:button type="button" variant="ghost" wire:click="saveCurrentStep" wire:loading.attr="disabled" wire:target="saveCurrentStep,nextStep,complete" icon="bookmark">
                    {{ __('guest_intake.actions.save_draft') }}
                </flux:button>

                @if($step < 6)
                    <flux:button type="button" variant="primary" icon="arrow-right" wire:click="nextStep" wire:loading.attr="disabled" wire:target="saveCurrentStep,nextStep">
                        {{ __('guest_intake.actions.next') }}
                    </flux:button>
                @else
                    <flux:button type="button" variant="primary" icon="check" wire:click="complete" wire:loading.attr="disabled" wire:target="complete">
                        {{ __('guest_intake.actions.complete') }}
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>
</section>
