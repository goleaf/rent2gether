@if($conversation->has_urgent_messages)
    <flux:callout color="red" icon="exclamation-triangle">
        <flux:callout.text>{{ __('messages.messages.urgent_message') }}</flux:callout.text>
    </flux:callout>
@endif
