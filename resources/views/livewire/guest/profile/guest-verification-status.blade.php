<div class="space-y-3">
    <flux:heading size="md">{{ __('verifications.title') }}</flux:heading>

    @forelse ($statuses as $status)
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text>{{ $status['type_label'] }}</flux:text>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $status['status_label'] }}</flux:text>
        </div>
    @empty
        <flux:text>{{ __('verifications.empty') }}</flux:text>
    @endforelse
</div>
