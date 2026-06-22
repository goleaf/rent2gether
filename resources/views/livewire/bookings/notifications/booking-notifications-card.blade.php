<section class="space-y-3">
    <flux:heading size="lg">{{ __('notifications.sections.booking') }}</flux:heading>

    <div class="space-y-2">
        @forelse($notifications as $notification)
            <div class="rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950">
                <flux:heading size="sm">{{ $notification->title() }}</flux:heading>
                <flux:text size="sm">{{ $notification->body() }}</flux:text>
            </div>
        @empty
            <flux:text size="sm">{{ __('notifications.empty_states.booking') }}</flux:text>
        @endforelse
    </div>
</section>
