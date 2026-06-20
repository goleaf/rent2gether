<section class="space-y-4" aria-labelledby="guest-intake-title">
    <flux:card class="space-y-4">
        <div class="space-y-2">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 space-y-1">
                    <flux:badge color="emerald">{{ __('guest_intake.badge') }}</flux:badge>
                    <flux:heading id="guest-intake-title" size="lg">{{ __('guest_intake.title') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.helper') }}</flux:text>
                </div>
                <flux:badge color="zinc">{{ __('guest_intake.progress', ['current' => $step, 'total' => 6]) }}</flux:badge>
            </div>

            <flux:progress value="{{ (int) round(($step / 6) * 100) }}" />
        </div>

        @if($statusMessage)
            <flux:callout color="emerald" icon="check-circle">
                <flux:callout.text>{{ $statusMessage }}</flux:callout.text>
            </flux:callout>
        @endif

        <div wire:loading.delay class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-sm text-sky-800 dark:border-sky-900 dark:bg-sky-950 dark:text-sky-200">
            {{ __('guest_intake.messages.saving') }}
        </div>

        @if($step === 1)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.trip_purpose') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.trip_purpose') }}</flux:text>
                </div>

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.trip_purpose') }}</flux:label>
                    <flux:select wire:model.change="tripPurpose">
                        <option value="">{{ __('guest_intake.placeholders.choose') }}</option>
                        @foreach($tripPurposes as $purpose)
                            <option value="{{ $purpose }}">{{ __('guest_intake.trip_purposes.'.$purpose) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tripPurpose" />
                </flux:field>

                @if($tripPurpose === 'other')
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.trip_purpose_other') }}</flux:label>
                        <flux:textarea rows="2" wire:model.blur="tripPurposeOther" />
                        <flux:error name="tripPurposeOther" />
                    </flux:field>
                @endif

                @if($tripPurpose === 'medical')
                    <flux:callout color="amber" icon="shield-check">
                        <flux:callout.heading>{{ __('guest_intake.privacy.medical_title') }}</flux:callout.heading>
                        <flux:callout.text>{{ __('guest_intake.privacy.medical_helper') }}</flux:callout.text>
                    </flux:callout>
                @endif

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.trip_purpose_visibility') }}</flux:label>
                    <flux:select wire:model.change="tripPurposeVisibility">
                        <option value="safe">{{ __('guest_intake.visibility.safe') }}</option>
                        <option value="exact">{{ __('guest_intake.visibility.exact') }}</option>
                    </flux:select>
                    <flux:description>{{ __('guest_intake.privacy.visibility_helper') }}</flux:description>
                    <flux:error name="tripPurposeVisibility" />
                </flux:field>
            </div>
        @elseif($step === 2)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.arrival_departure') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.arrival_departure') }}</flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.planned_arrival_date') }}</flux:label>
                        <flux:input type="date" wire:model.change="plannedArrivalDate" />
                        <flux:error name="plannedArrivalDate" />
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.planned_arrival_time') }}</flux:label>
                        <flux:input type="time" wire:model.change="plannedArrivalTime" />
                        <flux:error name="plannedArrivalTime" />
                    </flux:field>
                </div>

                <flux:checkbox wire:model.change="arrivalTimeUnknown" label="{{ __('guest_intake.fields.arrival_time_unknown') }}" />

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.planned_arrival_window') }}</flux:label>
                    <flux:input wire:model.blur="plannedArrivalWindow" maxlength="100" />
                    <flux:error name="plannedArrivalWindow" />
                </flux:field>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.planned_departure_time') }}</flux:label>
                        <flux:input type="time" wire:model.change="plannedDepartureTime" />
                        <flux:error name="plannedDepartureTime" />
                    </flux:field>
                    <div class="space-y-3 pt-1">
                        <flux:checkbox wire:model.change="departureTimeUnknown" label="{{ __('guest_intake.fields.departure_time_unknown') }}" />
                        <flux:checkbox wire:model.change="canAdjustArrivalTime" label="{{ __('guest_intake.fields.can_adjust_arrival_time') }}" />
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:checkbox wire:model.change="earlyCheckInRequested" label="{{ __('guest_intake.fields.early_check_in_requested') }}" />
                        <flux:field>
                            <flux:label>{{ __('guest_intake.fields.requested_early_check_in_time') }}</flux:label>
                            <flux:input type="time" wire:model.change="requestedEarlyCheckInTime" />
                        </flux:field>
                    </div>
                    <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                        <flux:checkbox wire:model.change="lateCheckOutRequested" label="{{ __('guest_intake.fields.late_check_out_requested') }}" />
                        <flux:field>
                            <flux:label>{{ __('guest_intake.fields.requested_late_check_out_time') }}</flux:label>
                            <flux:input type="time" wire:model.change="requestedLateCheckOutTime" />
                        </flux:field>
                    </div>
                </div>
            </div>
        @elseif($step === 3)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.baggage_pets_smoking') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.baggage_pets_smoking') }}</flux:text>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.baggage_level') }}</flux:label>
                        <flux:select wire:model.change="baggageLevel">
                            <option value="">{{ __('guest_intake.placeholders.choose') }}</option>
                            @foreach($baggageLevels as $level)
                                <option value="{{ $level }}">{{ __('guest_intake.baggage.'.$level) }}</option>
                            @endforeach
                        </flux:select>
                    </flux:field>
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.baggage_count') }}</flux:label>
                        <flux:input type="number" min="0" max="20" inputmode="numeric" wire:model.change="baggageCount" />
                    </flux:field>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:checkbox wire:model.change="hasLargeSuitcase" label="{{ __('guest_intake.fields.has_large_suitcase') }}" />
                    <flux:checkbox wire:model.change="hasSpecialBaggage" label="{{ __('guest_intake.fields.has_special_baggage') }}" />
                    <flux:checkbox wire:model.change="needsLuggageStorageBeforeCheckin" label="{{ __('guest_intake.fields.needs_luggage_storage_before_checkin') }}" />
                    <flux:checkbox wire:model.change="needsLuggageStorageAfterCheckout" label="{{ __('guest_intake.fields.needs_luggage_storage_after_checkout') }}" />
                </div>

                @if($hasSpecialBaggage)
                    <flux:field>
                        <flux:label>{{ __('guest_intake.fields.special_baggage_type') }}</flux:label>
                        <flux:input wire:model.blur="specialBaggageType" maxlength="100" />
                    </flux:field>
                @endif

                <div class="space-y-3 rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <flux:checkbox wire:model.change="hasPet" label="{{ __('guest_intake.fields.has_pet') }}" />
                    @if($hasPet)
                        <div class="grid gap-3 sm:grid-cols-2">
                            <flux:field>
                                <flux:label>{{ __('guest_intake.fields.pet_type') }}</flux:label>
                                <flux:select wire:model.change="petType">
                                    <option value="">{{ __('guest_intake.placeholders.choose') }}</option>
                                    @foreach($petTypes as $type)
                                        <option value="{{ $type }}">{{ __('guest_intake.pet_types.'.$type) }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                            <flux:field>
                                <flux:label>{{ __('guest_intake.fields.pet_size') }}</flux:label>
                                <flux:select wire:model.change="petSize">
                                    <option value="">{{ __('guest_intake.placeholders.choose') }}</option>
                                    @foreach($petSizes as $size)
                                        <option value="{{ $size }}">{{ __('guest_intake.pet_sizes.'.$size) }}</option>
                                    @endforeach
                                </flux:select>
                            </flux:field>
                        </div>
                        <flux:field>
                            <flux:label>{{ __('guest_intake.fields.pet_notes') }}</flux:label>
                            <flux:textarea rows="2" wire:model.blur="petNotes" />
                        </flux:field>
                    @endif
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:checkbox wire:model.change="smokes" label="{{ __('guest_intake.fields.smokes') }}" />
                    <flux:checkbox wire:model.change="acceptsSmokingRules" label="{{ __('guest_intake.fields.accepts_smoking_rules') }}" />
                </div>
            </div>
        @elseif($step === 4)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.comfort_work') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.comfort_work') }}</flux:text>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:checkbox wire:model.change="needsQuiet" label="{{ __('guest_intake.fields.needs_quiet') }}" />
                    <flux:checkbox wire:model.change="needsWorkspace" label="{{ __('guest_intake.fields.needs_workspace') }}" />
                    <flux:checkbox wire:model.change="needsFastWifi" label="{{ __('guest_intake.fields.needs_fast_wifi') }}" />
                    <flux:checkbox wire:model.change="needsPowerSocket" label="{{ __('guest_intake.fields.needs_power_socket') }}" />
                    <flux:checkbox wire:model.change="needsOnlineCalls" label="{{ __('guest_intake.fields.needs_online_calls') }}" />
                    <flux:checkbox wire:model.change="needsLateEntry" label="{{ __('guest_intake.fields.needs_late_entry') }}" />
                    <flux:checkbox wire:model.change="needsSelfCheckIn" label="{{ __('guest_intake.fields.needs_self_check_in') }}" />
                </div>

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.noise_sensitivity_level') }}</flux:label>
                    <flux:select wire:model.change="noiseSensitivityLevel">
                        <option value="">{{ __('guest_intake.placeholders.choose') }}</option>
                        @foreach($noiseLevels as $level)
                            <option value="{{ $level }}">{{ __('guest_intake.noise_sensitivity.'.$level) }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        @elseif($step === 5)
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.documents_special_requests') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.documents_special_requests') }}</flux:text>
                </div>

                <div class="grid gap-2 sm:grid-cols-2">
                    <flux:checkbox wire:model.change="needsRegistration" label="{{ __('guest_intake.fields.needs_registration') }}" />
                    <flux:checkbox wire:model.change="needsWorkDocuments" label="{{ __('guest_intake.fields.needs_work_documents') }}" />
                    <flux:checkbox wire:model.change="needsInvoice" label="{{ __('guest_intake.fields.needs_invoice') }}" />
                    <flux:checkbox wire:model.change="needsReceipt" label="{{ __('guest_intake.fields.needs_receipt') }}" />
                    <flux:checkbox wire:model.change="needsContract" label="{{ __('guest_intake.fields.needs_contract') }}" />
                </div>

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.company_name') }}</flux:label>
                    <flux:input wire:model.blur="companyName" maxlength="255" />
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.document_notes') }}</flux:label>
                    <flux:textarea rows="2" wire:model.blur="documentNotes" />
                    <flux:description>{{ __('guest_intake.privacy.document_notes_helper') }}</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.special_requests') }}</flux:label>
                    <flux:textarea rows="3" wire:model.blur="specialRequests" />
                </flux:field>
            </div>
        @else
            <div class="space-y-4">
                <div class="space-y-1">
                    <flux:heading size="sm">{{ __('guest_intake.steps.host_message') }}</flux:heading>
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('guest_intake.step_helpers.host_message') }}</flux:text>
                </div>

                <div class="flex flex-wrap gap-2">
                    <flux:button type="button" size="sm" variant="ghost" icon="sparkles" wire:click="generateHostMessage">
                        {{ __('guest_intake.actions.generate_message') }}
                    </flux:button>
                    @foreach($templates as $template)
                        <flux:button type="button" size="sm" variant="ghost" wire:click="$set('hostMessage', @js($template))">
                            {{ $template }}
                        </flux:button>
                    @endforeach
                </div>

                <flux:field>
                    <flux:label>{{ __('guest_intake.fields.host_message') }}</flux:label>
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

                <flux:checkbox wire:model.change="rulesAccepted" label="{{ __('guest_intake.fields.rules_accepted') }}" />
                <flux:error name="rules_accepted" />
            </div>
        @endif

        <div class="flex items-center justify-between gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700">
            <flux:button type="button" variant="ghost" icon="arrow-left" wire:click="previousStep" :disabled="$step === 1">
                {{ __('guest_intake.actions.previous') }}
            </flux:button>

            <div class="flex gap-2">
                <flux:button type="button" variant="ghost" wire:click="saveCurrentStep" wire:loading.attr="disabled" wire:target="saveCurrentStep,nextStep,complete">
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
