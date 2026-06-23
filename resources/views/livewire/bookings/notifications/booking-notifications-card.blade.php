<section class="space-y-3">
    <flux:heading size="lg">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="calendar-days" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('notifications.sections.booking') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="calendar-days" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $notification->title() }}</span>
                    </span>
                </flux:heading>
                <flux:text size="sm">{{ $notification->body() }}</flux:text>
            </div>
        @empty
            <flux:text size="sm">{{ __('notifications.empty_states.booking') }}</flux:text>
        @endforelse
    </div>
</section>
