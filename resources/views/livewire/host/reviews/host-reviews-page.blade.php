<x-ui.page class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">{{ __('reviews.title') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('reviews.messages.host_reviews_helper') }}</flux:text>
    </header>

    <livewire:host.reviews.host-reputation-summary :host-user-id="auth()->id()" />
</x-ui.page>
