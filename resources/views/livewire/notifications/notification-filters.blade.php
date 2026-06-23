<div class="flex gap-2 overflow-x-auto">
    @foreach(['all', 'unread', 'urgent', 'booking', 'message'] as $filter)
        <flux:badge icon="bell">{{ __('notifications.filters.'.$filter) }}</flux:badge>
    @endforeach
</div>
