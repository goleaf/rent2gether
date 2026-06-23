<header class="space-y-2">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:heading size="lg" level="1">
                <span class="inline-flex min-w-0 items-center gap-2">
                    <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                    <span class="min-w-0">{{ __('messages.title') }}</span>
                </span>
            </flux:heading>
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge size="sm" icon="user">{{ __('messages.conversation_types.'.$conversation->conversation_type) }}</flux:badge>
                <flux:badge size="sm" icon="user">{{ __('messages.statuses.'.$conversation->status) }}</flux:badge>
            </div>
        </div>

        @if($conversation->has_urgent_messages)
            <flux:badge color="red" icon="exclamation-triangle">{{ __('messages.sections.urgent') }}</flux:badge>
        @endif
    </div>
</header>
