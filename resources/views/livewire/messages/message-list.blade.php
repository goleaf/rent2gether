<section class="space-y-3" wire:poll.visible.10s>
    @forelse($messages as $message)
        <livewire:messages.message-bubble :message="$message" :key="'message-bubble-'.$message['id']" />
    @empty
        <div class="rounded-lg border border-zinc-200 bg-white p-4 text-center dark:border-zinc-800 dark:bg-zinc-950">
            <flux:text>{{ __('messages.messages.no_messages') }}</flux:text>
        </div>
    @endforelse
</section>
