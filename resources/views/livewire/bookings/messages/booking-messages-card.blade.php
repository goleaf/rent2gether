<flux:card class="space-y-3 p-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('messages.title') }}</span>
            </span>
        </flux:heading>
        <livewire:messages.unread-badge :count="$conversation->guest_unread_count + $conversation->host_unread_count" :key="'booking-message-unread-'.$conversation->id" />
    </div>

    <livewire:messages.message-list :conversation="$conversation->id" :key="'booking-message-list-'.$conversation->id" />
</flux:card>
