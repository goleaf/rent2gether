<flux:card class="p-3">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <flux:badge size="sm" icon="user">{{ __('messages.conversation_types.'.$conversation->conversation_type) }}</flux:badge>
                @if($conversation->has_urgent_messages)
                    <flux:badge color="red" size="sm" icon="exclamation-triangle">{{ __('messages.sections.urgent') }}</flux:badge>
                @endif
            </div>

            <flux:text class="truncate font-medium">
                {{ $conversation->lastConversationMessage?->body ?: ($conversation->lastConversationMessage?->translation_key ? __($conversation->lastConversationMessage->translation_key, $conversation->lastConversationMessage->translation_params_json ?? []) : __('messages.messages.no_messages')) }}
            </flux:text>
        </div>
    </div>
</flux:card>
