<section class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <flux:heading size="lg" level="2">{{ __('messages.sections.urgent') }}</flux:heading>
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
