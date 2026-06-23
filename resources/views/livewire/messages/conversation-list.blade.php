<x-ui.page class="space-y-6">
    <flux:heading size="xl">
        <span class="inline-flex min-w-0 items-center gap-2">
            <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
            <span class="min-w-0">{{ __('search.messages.title') }}</span>
        </span>
    </flux:heading>

    <div class="space-y-2">
        @forelse($this->conversations as $conversation)
            <a href="{{ route('messages.show', ['locale' => app()->getLocale(), 'conversation' => $conversation]) }}"
               wire:navigate
               class="block">
                <flux:card class="flex items-center justify-between hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition">
                    <div class="flex items-center gap-3">
                        <div class="size-10 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                            <flux:icon name="user" class="size-5 text-zinc-400" />
                        </div>
                        <div>
                            <flux:text class="font-medium">{{ $conversation->otherParticipant(auth()->id())->name }}</flux:text>
                            @if($conversation->messages->last())
                                <flux:text size="sm" class="text-zinc-500 truncate max-w-xs">
                                    {{ Str::limit($conversation->messages->last()->body, 60) }}
                                </flux:text>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($conversation->unreadCountFor(auth()->id()) > 0)
                            <flux:badge color="red" size="sm" icon="exclamation-triangle">{{ $conversation->unreadCountFor(auth()->id()) }}</flux:badge>
                        @endif
                        @if($conversation->messages->last())
                            <flux:text size="sm" class="text-zinc-400">{{ $conversation->messages->last()->created_at->diffForHumans() }}</flux:text>
                        @endif
                    </div>
                </flux:card>
            </a>
        @empty
            <flux:card>
                <flux:text class="text-center text-zinc-500 py-8">{{ __('search.messages.empty') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</x-ui.page>
