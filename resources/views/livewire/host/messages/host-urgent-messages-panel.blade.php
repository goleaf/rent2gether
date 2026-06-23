<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">
            <span class="inline-flex min-w-0 items-center gap-2">
                <flux:icon name="chat-bubble-left-right" variant="mini" class="size-5 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                <span class="min-w-0">{{ __('messages.sections.urgent') }}</span>
            </span>
        </flux:heading>
    </div>

    <div class="space-y-2">
        @forelse($conversations as $conversation)
            <article class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900 dark:bg-red-950/30">
                <flux:text size="sm" class="font-medium text-red-900 dark:text-red-100">
                    {{ $conversation['guest_name'] ?: __('messages.inbox.unknown_user') }}
                </flux:text>

                <div class="mt-2 space-y-1">
                    @foreach($conversation['messages'] as $message)
                        <flux:text size="sm" class="text-red-800 dark:text-red-100">{{ $message['body'] }}</flux:text>
                    @endforeach
                </div>
            </article>
        @empty
            <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.empty_states.urgent') }}</flux:text>
        @endforelse
    </div>
</section>
