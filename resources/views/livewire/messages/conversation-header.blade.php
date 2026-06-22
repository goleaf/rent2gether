<header class="space-y-2">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="lg" level="1">{{ __('messages.title') }}</flux:heading>
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge size="sm">{{ __('messages.conversation_types.'.$conversation->conversation_type) }}</flux:badge>
                <flux:badge size="sm">{{ __('messages.statuses.'.$conversation->status) }}</flux:badge>
            </div>
        </div>

        @if($conversation->has_urgent_messages)
            <flux:badge color="red">{{ __('messages.sections.urgent') }}</flux:badge>
        @endif
    </div>
</header>
