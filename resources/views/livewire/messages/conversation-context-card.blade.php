<flux:card class="p-3">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0 space-y-1">
            <flux:text class="font-medium">{{ __('messages.fields.conversation') }}</flux:text>
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">
                {{ __('messages.conversation_types.'.$conversation->conversation_type) }}
            </flux:text>
        </div>
        <flux:badge size="sm" icon="calendar-days">{{ $conversation->booking?->booking_number ?: $conversation->booking?->reference }}</flux:badge>
    </div>
</flux:card>
