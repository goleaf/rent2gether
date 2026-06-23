<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_requests.host_response.title') }}</span>
        </span>
    </flux:heading>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.host_response') }}</span>
    </span>
</flux:label>
        <flux:textarea rows="3" wire:model.blur="message" />
        <flux:error name="message" />
    </flux:field>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="primary" wire:click="approve" icon="calendar-days">{{ __('booking_requests.actions.approve') }}</flux:button>
        <flux:button type="button" variant="outline" wire:click="askQuestion" icon="chat-bubble-left-right">{{ __('booking_requests.actions.ask_question') }}</flux:button>
    </div>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.proposed_check_in_time') }}</span>
    </span>
</flux:label>
            <flux:input type="time" wire:model.change="proposedCheckInTime" icon="calendar-days" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.proposed_check_out_time') }}</span>
    </span>
</flux:label>
            <flux:input type="time" wire:model.change="proposedCheckOutTime" icon="calendar-days" />
        </flux:field>
    </div>
    <flux:button type="button" variant="outline" class="w-full" wire:click="proposeTimeChange" icon="calendar-days">{{ __('booking_requests.actions.propose_time_change') }}</flux:button>

    <div class="grid gap-3 sm:grid-cols-2">
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.proposed_check_in_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="proposedCheckInDate" icon="calendar-days" />
        </flux:field>
        <flux:field>
            <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.proposed_check_out_date') }}</span>
    </span>
</flux:label>
            <flux:input type="date" wire:model.change="proposedCheckOutDate" icon="calendar-days" />
        </flux:field>
    </div>
    <flux:button type="button" variant="outline" class="w-full" wire:click="proposeDateChange" icon="calendar-days">{{ __('booking_requests.actions.propose_date_change') }}</flux:button>

    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('booking_requests.fields.rejection_reason') }}</span>
    </span>
</flux:label>
        <flux:select wire:model.change="rejectionReason">
            <flux:select.option value="dates_unavailable">{{ __('booking_requests.rejection_reasons.dates_unavailable') }}</flux:select.option>
            <flux:select.option value="place_not_ready">{{ __('booking_requests.rejection_reasons.place_not_ready') }}</flux:select.option>
            <flux:select.option value="too_many_guests">{{ __('booking_requests.rejection_reasons.too_many_guests') }}</flux:select.option>
            <flux:select.option value="host_unavailable">{{ __('booking_requests.rejection_reasons.host_unavailable') }}</flux:select.option>
            <flux:select.option value="other">{{ __('booking_requests.rejection_reasons.other') }}</flux:select.option>
        </flux:select>
    </flux:field>

    <div class="grid gap-2 sm:grid-cols-2">
        <flux:button type="button" variant="danger" wire:click="reject" icon="x-mark">{{ __('booking_requests.actions.reject') }}</flux:button>
        @if($request->status === 'approved')
            <flux:button type="button" variant="primary" wire:click="convert" icon="calendar-days">{{ __('booking_requests.actions.convert_to_booking') }}</flux:button>
        @endif
    </div>
</flux:card>
