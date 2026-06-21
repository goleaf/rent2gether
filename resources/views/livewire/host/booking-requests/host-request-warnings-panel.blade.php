<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_requests.host_view.warnings') }}</flux:heading>
    @forelse($warnings as $warning)
        <flux:callout variant="{{ $warning['severity'] === 'blocking' ? 'danger' : 'warning' }}">
            <flux:callout.heading>{{ $warning['message'] }}</flux:callout.heading>
        </flux:callout>
    @empty
        <flux:callout variant="success" :text="__('booking_requests.empty.no_host_warnings')" />
    @endforelse
</flux:card>
