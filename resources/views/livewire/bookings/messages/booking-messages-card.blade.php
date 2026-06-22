<flux:card class="space-y-3 p-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('messages.title') }}</flux:heading>
        <livewire:messages.unread-badge :count="$conversation->guest_unread_count + $conversation->host_unread_count" :key="'booking-message-unread-'.$conversation->id" />
    </div>

    <livewire:messages.message-list :conversation="$conversation->id" :key="'booking-message-list-'.$conversation->id" />
</flux:card>
