<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_requests.host_response.title') }}</flux:heading>

    <flux:field>
        <flux:label>{{ __('booking_requests.fields.host_response') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="message" />
        <flux:error name="message" />
    </flux:field>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="primary" wire:click="approve">{{ __('booking_requests.actions.approve') }}</flux:button>
        <flux:button type="button" variant="outline" wire:click="askQuestion">{{ __('booking_requests.actions.ask_question') }}</flux:button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('booking_requests.fields.proposed_check_in_time') }}</flux:label>
            <flux:input type="time" wire:model.change="proposedCheckInTime" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('booking_requests.fields.proposed_check_out_time') }}</flux:label>
            <flux:input type="time" wire:model.change="proposedCheckOutTime" />
        </flux:field>
    </div>
    <flux:button type="button" variant="outline" class="w-full" wire:click="proposeTimeChange">{{ __('booking_requests.actions.propose_time_change') }}</flux:button>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>{{ __('booking_requests.fields.proposed_check_in_date') }}</flux:label>
            <flux:input type="date" wire:model.change="proposedCheckInDate" />
        </flux:field>
        <flux:field>
            <flux:label>{{ __('booking_requests.fields.proposed_check_out_date') }}</flux:label>
            <flux:input type="date" wire:model.change="proposedCheckOutDate" />
        </flux:field>
    </div>
    <flux:button type="button" variant="outline" class="w-full" wire:click="proposeDateChange">{{ __('booking_requests.actions.propose_date_change') }}</flux:button>

    <flux:field>
        <flux:label>{{ __('booking_requests.fields.rejection_reason') }}</flux:label>
        <flux:select wire:model.change="rejectionReason">
            <flux:select.option value="dates_unavailable">{{ __('booking_requests.rejection_reasons.dates_unavailable') }}</flux:select.option>
            <flux:select.option value="place_not_ready">{{ __('booking_requests.rejection_reasons.place_not_ready') }}</flux:select.option>
            <flux:select.option value="too_many_guests">{{ __('booking_requests.rejection_reasons.too_many_guests') }}</flux:select.option>
            <flux:select.option value="host_unavailable">{{ __('booking_requests.rejection_reasons.host_unavailable') }}</flux:select.option>
            <flux:select.option value="other">{{ __('booking_requests.rejection_reasons.other') }}</flux:select.option>
        </flux:select>
    </flux:field>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="danger" wire:click="reject">{{ __('booking_requests.actions.reject') }}</flux:button>
        @if($request->status === 'approved')
            <flux:button type="button" variant="primary" wire:click="convert">{{ __('booking_requests.actions.convert_to_booking') }}</flux:button>
        @endif
    </div>
</flux:card>
