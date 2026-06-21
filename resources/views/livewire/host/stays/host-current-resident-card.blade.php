<flux:card class="space-y-3">
    @if (isset($stays))
        <flux:heading size="md">{{ __('stays.components.checkout_soon') }}</flux:heading>
        @forelse ($stays as $stay)
            <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                <flux:text class="font-medium">{{ $stay->guest?->name }}</flux:text>
                <flux:text size="sm">{{ $stay->room?->title }} · {{ $stay->planned_check_out_date?->format('M j') }}</flux:text>
            </div>
        @empty
            <flux:text size="sm">{{ __('stays.empty.no_checkout_soon') }}</flux:text>
        @endforelse
    @elseif ($summary)
        <div class="flex items-start justify-between gap-3">
            <div>
                <flux:heading size="md">{{ __('stays.host_title') }}</flux:heading>
                <flux:text size="sm">{{ $summary['sleeping_place'] }} · {{ $summary['room'] }}</flux:text>
            </div>
            <flux:badge>{{ $summary['status'] }}</flux:badge>
        </div>
        <flux:text size="sm">{{ __('stays.fields.nights_remaining') }}: {{ $summary['nights_remaining'] }}</flux:text>
    @else
        <flux:text>{{ __('stays.empty.no_current_residents') }}</flux:text>
    @endif
</flux:card>
