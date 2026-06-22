<x-ui.page class="space-y-0 flex min-h-[calc(100vh-6rem)] flex-col gap-4">
    <header class="space-y-2">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0 space-y-1">
                <flux:heading size="xl" level="1">{{ __('messages.title') }}</flux:heading>
                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge size="sm">{{ __('messages.conversation_types.'.$conversation->conversation_type) }}</flux:badge>
                    @if($placeTitle)
                        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ $placeTitle }}</flux:text>
                    @endif
                </div>
            </div>

            @if($conversation->has_urgent_messages)
                <flux:badge color="red">{{ __('messages.messages.urgent_message') }}</flux:badge>
            @endif
        </div>
    </header>

    <section class="flex-1 space-y-3 overflow-y-auto rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-950" wire:poll.visible.10s>
        @forelse($messageCards as $card)
            <article class="{{ $card['mine'] ? 'ml-auto' : 'mr-auto' }} max-w-[88%] space-y-1">
                @if($card['is_system'])
                    <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
                        <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">{{ $card['body'] }}</flux:text>
                    </div>
                @else
                    <div class="{{ $card['mine'] ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800 dark:text-zinc-50' }} rounded-lg px-3 py-2">
                        @if($card['is_important'])
                            <div class="mb-1 text-xs font-medium opacity-80">{{ __('messages.messages.important_message') }}</div>
                        @endif
                        <p class="whitespace-pre-line text-sm">{{ $card['body'] }}</p>
                    </div>
                @endif
            </article>
        @empty
            <div class="flex min-h-48 items-center justify-center">
                <flux:text class="text-center text-zinc-500 dark:text-zinc-400">{{ __('messages.messages.no_messages') }}</flux:text>
            </div>
        @endforelse
    </section>
</x-ui.page>
