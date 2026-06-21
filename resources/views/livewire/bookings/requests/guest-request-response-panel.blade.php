<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_requests.guest_response.title') }}</flux:heading>

    <flux:field>
        <flux:label>{{ __('booking_requests.fields.message_to_host') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="message" />
        <flux:error name="message" />
    </flux:field>

    @if($canRespond)
        <div class="grid gap-2 sm:grid-cols-3">
            <flux:button type="button" variant="primary" wire:click="answer">{{ __('booking_requests.actions.answer_question') }}</flux:button>
            <flux:button type="button" variant="outline" wire:click="accept">{{ __('booking_requests.actions.accept_proposal') }}</flux:button>
            <flux:button type="button" variant="danger" wire:click="reject">{{ __('booking_requests.actions.reject_proposal') }}</flux:button>
        </div>
    @endif

    @if($canWithdraw)
        <flux:button type="button" variant="ghost" class="w-full" wire:click="withdraw">{{ __('booking_requests.actions.withdraw') }}</flux:button>
    @endif
</flux:card>
