<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_requests.guest_response.title') }}</span>
        </span>
    </flux:heading>

    <flux:field>
        <flux:label>{{ __('booking_requests.fields.message_to_host') }}</flux:label>
        <flux:textarea rows="3" wire:model.blur="message" />
        <flux:error name="message" />
    </flux:field>

    @if($canRespond)
        <div class="grid gap-2 sm:grid-cols-3">
            <flux:button type="button" variant="primary" wire:click="answer" icon="chat-bubble-left-right">{{ __('booking_requests.actions.answer_question') }}</flux:button>
            <flux:button type="button" variant="outline" wire:click="accept" icon="calendar-days">{{ __('booking_requests.actions.accept_proposal') }}</flux:button>
            <flux:button type="button" variant="danger" wire:click="reject" icon="x-mark">{{ __('booking_requests.actions.reject_proposal') }}</flux:button>
        </div>
    @endif

    @if($canWithdraw)
        <flux:button type="button" variant="ghost" class="w-full" wire:click="withdraw" icon="x-mark">{{ __('booking_requests.actions.withdraw') }}</flux:button>
    @endif
</flux:card>
