<flux:card class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="sm">{{ __('payments.host.status_title') }}</flux:heading>
        <flux:badge color="{{ $summary['status_color'] }}">{{ $summary['status'] }}</flux:badge>
    </div>

    <flux:text size="sm">{{ $summary['amount'] }}</flux:text>
</flux:card>
