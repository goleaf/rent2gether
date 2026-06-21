<div class="space-y-2">
    @forelse($warnings as $warning)
        <flux:callout variant="{{ $warning['severity'] === 'blocking' ? 'danger' : 'warning' }}">
            <flux:callout.heading>{{ $warning['message'] }}</flux:callout.heading>
        </flux:callout>
    @empty
        <flux:callout variant="success" :text="__('booking_requests.empty.no_visible_warnings')" />
    @endforelse
</div>
