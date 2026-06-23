<x-ui.page class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="star" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('reviews.title') }}</span>
            </span>
        </flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.host_reviews_helper') }}</flux:text>
    </header>

    <livewire:host.reviews.host-reputation-summary :host-user-id="auth()->id()" />
</x-ui.page>
