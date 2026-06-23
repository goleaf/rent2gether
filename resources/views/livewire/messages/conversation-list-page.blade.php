<x-ui.page class="space-y-4">
    <header class="space-y-1">
        <flux:heading size="xl" level="1">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('messages.title') }}</span>
            </span>
        </flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.inbox.helper') }}</flux:text>
    </header>

    <section class="space-y-2">
        @forelse($conversations as $conversation)
            <livewire:messages.conversation-list-item :conversation="$conversation->id" :key="'conversation-list-'.$conversation->id" />
        @empty
            <div class="rounded-lg border border-zinc-200 bg-white p-4 text-center dark:border-zinc-800 dark:bg-zinc-950">
                <flux:text>{{ __('messages.empty_states.conversations') }}</flux:text>
            </div>
        @endforelse
    </section>
</x-ui.page>
