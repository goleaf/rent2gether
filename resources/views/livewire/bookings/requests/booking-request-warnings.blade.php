<div class="space-y-2">
    @forelse($warnings as $warning)
        <flux:callout variant="{{ $warning['severity'] === 'blocking' ? 'danger' : 'warning' }}" icon="chat-bubble-left-right">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ $warning['message'] }}</flux:callout.heading>
        </flux:callout>
    @empty
        <flux:callout variant="success" :text="__('booking_requests.empty.no_visible_warnings')"  icon="check-circle" />
    @endforelse
</div>
