<flux:card class="space-y-3">
    <flux:heading size="sm">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="exclamation-triangle" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('booking_requests.host_view.warnings') }}</span>
        </span>
    </flux:heading>
    @forelse($warnings as $warning)
        <flux:callout variant="{{ $warning['severity'] === 'blocking' ? 'danger' : 'warning' }}" icon="chat-bubble-left-right">
            <flux:callout.heading icon="exclamation-triangle" icon:variant="mini">{{ $warning['message'] }}</flux:callout.heading>
        </flux:callout>
    @empty
        <flux:callout variant="success" :text="__('booking_requests.empty.no_host_warnings')"  icon="check-circle" />
    @endforelse
</flux:card>
