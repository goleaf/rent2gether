<div class="mx-auto max-w-3xl space-y-4 px-4 py-4 sm:px-6">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">{{ __('messages.title') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.inbox.helper') }}</flux:text>
    </header>

    <livewire:host.messages.host-conversation-filters />
    <livewire:host.messages.host-urgent-messages-panel />

    <section class="space-y-2">
        @forelse($conversations as $conversation)
            <livewire:messages.conversation-list-item :conversation="$conversation->id" :key="'host-conversation-'.$conversation->id" />
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-center dark:border-zinc-800 dark:bg-zinc-950">
                <flux:text>{{ __('messages.empty_states.conversations') }}</flux:text>
            </div>
        @endforelse
    </section>
</div>
