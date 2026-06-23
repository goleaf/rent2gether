<div class="space-y-2">
    @forelse($reasons as $reason)
        <flux:callout icon="chat-bubble-left-right">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('availability.warnings.title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('availability.messages.'.$reason) }}</flux:callout.text>
        </flux:callout>
    @empty
        <flux:callout icon="chat-bubble-left-right">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ __('availability.messages.ready_title') }}</flux:callout.heading>
            <flux:callout.text>{{ __('availability.messages.ready_text') }}</flux:callout.text>
        </flux:callout>
    @endforelse
</div>
