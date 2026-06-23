<div class="space-y-3">
    <flux:heading size="md">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="shield-check" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('verifications.title') }}</span>
        </span>
    </flux:heading>

    @forelse ($statuses as $status)
        <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:text>{{ $status['type_label'] }}</flux:text>
            <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $status['status_label'] }}</flux:text>
        </div>
    @empty
        <flux:text>{{ __('verifications.empty') }}</flux:text>
    @endforelse
</div>
