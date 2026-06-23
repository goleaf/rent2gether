<form wire:submit="save" class="space-y-5">
    <flux:card class="space-y-4">
        <div>
            <flux:heading size="lg">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="home-modern" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('property.steps.access.title') }}</span>
                </span>
            </flux:heading>
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ __('property.steps.access.helper') }}</flux:text>
        </div>

        @if($wasSaved)
            <flux:callout color="emerald" icon="chat-bubble-left-right">
                <flux:callout.text>{{ __('property.messages.saved') }}</flux:callout.text>
            </flux:callout>
        @endif

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.entrance_type') }}</span>
    </span>
</flux:label>
            <flux:select wire:model.change="entranceType">
                <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                @foreach(['shared_entrance', 'private_entrance', 'through_yard', 'through_reception', 'code_door', 'electronic_lock'] as $type)
                    <flux:select.option value="{{ $type }}">{{ __('property.entrance_types.'.$type) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="entranceType" />
        </flux:field>

        <div class="space-y-3">
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasIntercom" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_intercom') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasIntercom" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasDoorCode" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_door_code') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasDoorCode" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasKey" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_key') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasKey" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasKeycard" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_keycard') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasKeycard" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasElectronicLock" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_electronic_lock') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasElectronicLock" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasKeySafe" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_key_safe') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasKeySafe" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="selfCheckInAvailable" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.self_check_in_available') }}</span>
                    </span>
                </flux:label>
                <flux:error name="selfCheckInAvailable" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="selfCheckInAvailableAtNight" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.self_check_in_available_at_night') }}</span>
                    </span>
                </flux:label>
                <flux:error name="selfCheckInAvailableAtNight" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="meetHostRequired" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.meet_host_required') }}</span>
                    </span>
                </flux:label>
                <flux:error name="meetHostRequired" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="meetHostRepresentativeRequired" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.meet_host_representative_required') }}</span>
                    </span>
                </flux:label>
                <flux:error name="meetHostRepresentativeRequired" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="access247" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.access_24_7') }}</span>
                    </span>
                </flux:label>
                <flux:error name="access247" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="canReturnAtNight" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.can_return_at_night') }}</span>
                    </span>
                </flux:label>
                <flux:error name="canReturnAtNight" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="hasNightEntryRestrictions" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.has_night_entry_restrictions') }}</span>
                    </span>
                </flux:label>
                <flux:error name="hasNightEntryRestrictions" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="guestVisitorsAllowed" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.guest_visitors_allowed') }}</span>
                    </span>
                </flux:label>
                <flux:error name="guestVisitorsAllowed" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="guestVisitorsNeedApproval" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.guest_visitors_need_approval') }}</span>
                    </span>
                </flux:label>
                <flux:error name="guestVisitorsNeedApproval" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="courierRulesEnabled" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.courier_rules_enabled') }}</span>
                    </span>
                </flux:label>
                <flux:error name="courierRulesEnabled" />
            </flux:field>
                        <flux:field variant="inline">
                <flux:checkbox wire:model.change="deliveryAllowed" />
                <flux:label>
                    <span class="inline-flex min-w-0 items-center gap-1.5">
                        <flux:icon name="key" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ __('property.fields.delivery_allowed') }}</span>
                    </span>
                </flux:label>
                <flux:error name="deliveryAllowed" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.key_safe_location_note') }}</span>
    </span>
</flux:label>
            <flux:textarea rows="3" wire:model.blur="keySafeLocationNote" />
            <flux:description>
                        <span class="inline-flex min-w-0 items-start gap-1.5">
                            <flux:icon name="information-circle" variant="mini" class="mt-0.5 size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                            <span class="min-w-0">
                                {{ __('property.helpers.private_access_note') }}
                            </span>
                        </span>
                    </flux:description>
            <flux:error name="keySafeLocationNote" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.night_entry_restriction_text') }}</span>
    </span>
</flux:label>
            <flux:textarea rows="3" wire:model.blur="nightEntryRestrictionText" />
            <flux:error name="nightEntryRestrictionText" />
        </flux:field>

        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="cog-6-tooth" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('property.fields.delivery_dropoff_location') }}</span>
    </span>
</flux:label>
            <flux:input wire:model.blur="deliveryDropoffLocation" icon="map-pin" />
            <flux:error name="deliveryDropoffLocation" />
        </flux:field>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
