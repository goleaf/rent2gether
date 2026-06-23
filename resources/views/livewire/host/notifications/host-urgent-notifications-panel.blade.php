<section class="space-y-3" wire:poll.visible.30s>
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="bell" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('notifications.sections.urgent') }}</span>
            </span>
        </flux:heading>
        <flux:badge color="red" icon="exclamation-triangle">{{ $notifications->count() }}</flux:badge>
    </div>

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="rounded-lg border border-red-200 bg-red-50/70 p-3 dark:border-red-400/20 dark:bg-red-400/10">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="bell" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $notification->title() }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">{{ $notification->body() }}</flux:text>
            </div>
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:text size="sm">{{ __('notifications.empty_states.urgent') }}</flux:text>
            </div>
        @endforelse
    </div>
</section>
