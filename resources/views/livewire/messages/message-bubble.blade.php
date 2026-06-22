<article class="{{ ($message['mine'] ?? false) ? 'ml-auto' : 'mr-auto' }} max-w-[88%]">
    @if($message['is_system'] ?? false)
        <div class="rounded-lg bg-zinc-50 px-3 py-2 text-center dark:bg-zinc-900">
            <flux:text size="sm" class="text-zinc-600 dark:text-zinc-300">{{ $message['body'] ?? '' }}</flux:text>
        </div>
    @else
        <div class="{{ ($message['mine'] ?? false) ? 'bg-emerald-600 text-white' : 'bg-zinc-100 text-zinc-950 dark:bg-zinc-800 dark:text-zinc-50' }} rounded-lg px-3 py-2">
            @if($message['is_important'] ?? false)
                <div class="mb-1 text-xs font-medium opacity-80">{{ __('messages.messages.important_message') }}</div>
            @endif
            <p class="whitespace-pre-line text-sm">{{ $message['body'] ?? '' }}</p>
        </div>
    @endif
</article>
