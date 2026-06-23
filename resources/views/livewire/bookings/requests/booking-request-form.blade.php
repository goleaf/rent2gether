<div class="space-y-4">
    @if($submittedRequestId)
        <flux:callout variant="success" icon="check-circle">
            <flux:callout.heading>{{ __('booking_requests.messages.request_sent') }}</flux:callout.heading>
            <flux:callout.text>{{ __('booking_requests.messages.host_reply_deadline') }}</flux:callout.text>
        </flux:callout>
        <livewire:bookings.requests.booking-request-summary :request="$submittedRequestId" :key="'request-summary-'.$submittedRequestId" />
    @else
        <flux:card class="space-y-4">
            <div class="space-y-1">
                <flux:heading size="lg">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('booking_requests.title') }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm">{{ __('booking_requests.messages.form_helper') }}</flux:text>
            </div>

            <div class="rounded-lg bg-zinc-50 px-3 py-3 dark:bg-zinc-900">
                <flux:text size="sm">{{ $summary['place'] }} · {{ $summary['room'] }}</flux:text>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $summary['dates'] }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm">{{ trans_choice('booking_requests.messages.nights_count', $summary['nights_count'], ['count' => $summary['nights_count']]) }} · {{ $summary['total'] }}</flux:text>
            </div>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.request_type') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="requestType">
                    <flux:select.option value="host_approval">{{ __('booking_requests.request_types.host_approval') }}</flux:select.option>
                    <flux:select.option value="stay_request">{{ __('booking_requests.request_types.stay_request') }}</flux:select.option>
                    <flux:select.option value="long_term_request">{{ __('booking_requests.request_types.long_term_request') }}</flux:select.option>
                    <flux:select.option value="same_day_urgent">{{ __('booking_requests.request_types.same_day_urgent') }}</flux:select.option>
                    <flux:select.option value="request_only">{{ __('booking_requests.request_types.request_only') }}</flux:select.option>
                </flux:select>
                <flux:error name="requestType" />
            </flux:field>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.trip_purpose') }}</span>
    </span>
</flux:label>
                <flux:select wire:model.change="tripPurpose">
                    <flux:select.option value="">{{ __('booking_requests.empty.choose_trip_purpose') }}</flux:select.option>
                    <flux:select.option value="work">{{ __('booking_requests.trip_purposes.work') }}</flux:select.option>
                    <flux:select.option value="study">{{ __('booking_requests.trip_purposes.study') }}</flux:select.option>
                    <flux:select.option value="travel">{{ __('booking_requests.trip_purposes.travel') }}</flux:select.option>
                    <flux:select.option value="relocation">{{ __('booking_requests.trip_purposes.relocation') }}</flux:select.option>
                </flux:select>
            </flux:field>

            <div class="grid gap-3 sm:grid-cols-2">
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.planned_arrival_time') }}</span>
    </span>
</flux:label>
                    <flux:input type="time" wire:model.change="plannedArrivalTime" icon="clock" />
                    <flux:error name="plannedArrivalTime" />
                </flux:field>
                <flux:field>
                    <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.planned_departure_time') }}</span>
    </span>
</flux:label>
                    <flux:input type="time" wire:model.change="plannedDepartureTime" icon="clock" />
                </flux:field>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="hasBaggage" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.has_baggage') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="hasBaggage" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsLuggageStorage" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.needs_luggage_storage') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsLuggageStorage" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsEarlyCheckIn" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.needs_early_check_in') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsEarlyCheckIn" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsLateCheckout" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.needs_late_checkout') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsLateCheckout" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsResidenceRegistration" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.needs_residence_registration') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsResidenceRegistration" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="needsReportingDocuments" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.needs_reporting_documents') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="needsReportingDocuments" />
                </flux:field>
            </div>

            <flux:field>
                <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.message_to_host') }}</span>
    </span>
</flux:label>
                <flux:textarea rows="4" wire:model.blur="guestMessage" />
                <flux:error name="guestMessage" />
            </flux:field>

            <div class="space-y-2">
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="guestAgreedToRules" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.guest_agreed_to_rules') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="guestAgreedToRules" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="guestAgreedToCancellationPolicy" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.guest_agreed_to_cancellation_policy') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="guestAgreedToCancellationPolicy" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="guestAgreedToDepositPolicy" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="banknotes" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.guest_agreed_to_deposit_policy') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="guestAgreedToDepositPolicy" />
                </flux:field>
                                <flux:field variant="inline">
                    <flux:checkbox wire:model.change="holdDates" />
                    <flux:label>
                        <span class="inline-flex min-w-0 items-center gap-1.5">
                            <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">{{ __('booking_requests.fields.hold_dates') }}</span>
                        </span>
                    </flux:label>
                    <flux:error name="holdDates" />
                </flux:field>
            </div>

            <flux:button type="button" variant="primary" class="w-full" wire:click="submit" wire:loading.attr="disabled" icon="paper-airplane">
                <span wire:loading.remove wire:target="submit">{{ __('booking_requests.actions.send_request') }}</span>
                <span wire:loading wire:target="submit">{{ __('booking_requests.actions.sending') }}</span>
            </flux:button>
        </flux:card>
    @endif
</div>
