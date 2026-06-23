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
            <flux:label>{{ __('property.fields.entrance_type') }}</flux:label>
            <flux:select wire:model.change="entranceType">
                <flux:select.option value="">{{ __('property.options.not_specified') }}</flux:select.option>
                @foreach(['shared_entrance', 'private_entrance', 'through_yard', 'through_reception', 'code_door', 'electronic_lock'] as $type)
                    <flux:select.option value="{{ $type }}">{{ __('property.entrance_types.'.$type) }}</flux:select.option>
                @endforeach
            </flux:select>
            <flux:error name="entranceType" />
        </flux:field>

        <div class="space-y-3">
            <flux:checkbox wire:model.change="hasIntercom" label="{{ __('property.fields.has_intercom') }}" />
            <flux:checkbox wire:model.change="hasDoorCode" label="{{ __('property.fields.has_door_code') }}" />
            <flux:checkbox wire:model.change="hasKey" label="{{ __('property.fields.has_key') }}" />
            <flux:checkbox wire:model.change="hasKeycard" label="{{ __('property.fields.has_keycard') }}" />
            <flux:checkbox wire:model.change="hasElectronicLock" label="{{ __('property.fields.has_electronic_lock') }}" />
            <flux:checkbox wire:model.change="hasKeySafe" label="{{ __('property.fields.has_key_safe') }}" />
            <flux:checkbox wire:model.change="selfCheckInAvailable" label="{{ __('property.fields.self_check_in_available') }}" />
            <flux:checkbox wire:model.change="selfCheckInAvailableAtNight" label="{{ __('property.fields.self_check_in_available_at_night') }}" />
            <flux:checkbox wire:model.change="meetHostRequired" label="{{ __('property.fields.meet_host_required') }}" />
            <flux:checkbox wire:model.change="meetHostRepresentativeRequired" label="{{ __('property.fields.meet_host_representative_required') }}" />
            <flux:checkbox wire:model.change="access247" label="{{ __('property.fields.access_24_7') }}" />
            <flux:checkbox wire:model.change="canReturnAtNight" label="{{ __('property.fields.can_return_at_night') }}" />
            <flux:checkbox wire:model.change="hasNightEntryRestrictions" label="{{ __('property.fields.has_night_entry_restrictions') }}" />
            <flux:checkbox wire:model.change="guestVisitorsAllowed" label="{{ __('property.fields.guest_visitors_allowed') }}" />
            <flux:checkbox wire:model.change="guestVisitorsNeedApproval" label="{{ __('property.fields.guest_visitors_need_approval') }}" />
            <flux:checkbox wire:model.change="courierRulesEnabled" label="{{ __('property.fields.courier_rules_enabled') }}" />
            <flux:checkbox wire:model.change="deliveryAllowed" label="{{ __('property.fields.delivery_allowed') }}" />
        </div>

        <flux:field>
            <flux:label>{{ __('property.fields.key_safe_location_note') }}</flux:label>
            <flux:textarea rows="3" wire:model.blur="keySafeLocationNote" />
            <flux:description>{{ __('property.helpers.private_access_note') }}</flux:description>
            <flux:error name="keySafeLocationNote" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('property.fields.night_entry_restriction_text') }}</flux:label>
            <flux:textarea rows="3" wire:model.blur="nightEntryRestrictionText" />
            <flux:error name="nightEntryRestrictionText" />
        </flux:field>

        <flux:field>
            <flux:label>{{ __('property.fields.delivery_dropoff_location') }}</flux:label>
            <flux:input wire:model.blur="deliveryDropoffLocation" icon="map-pin" />
            <flux:error name="deliveryDropoffLocation" />
        </flux:field>
    </flux:card>

    <flux:button type="submit" variant="primary" class="w-full sm:w-auto" wire:loading.attr="disabled" icon="chat-bubble-left-right">
        <span wire:loading.remove wire:target="save">{{ __('property.actions.save_step') }}</span>
        <span wire:loading wire:target="save">{{ __('property.messages.saving') }}</span>
    </flux:button>
</form>
