<section class="space-y-3">
    <flux:field>
        <flux:label>
    <span class="inline-flex min-w-0 items-center gap-1.5">
        <flux:icon name="chat-bubble-left-right" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
        <span class="min-w-0">{{ __('messages.actions.search') }}</span>
    </span>
</flux:label>
        <flux:input wire:model.live.debounce.500ms="query" icon="magnifying-glass" />
    </flux:field>

    @if(trim($query) !== '')
        <div class="space-y-2">
            @forelse($results as $conversation)
                <livewire:messages.conversation-list-item :conversation="$conversation->id" :key="'conversation-search-'.$conversation->id" />
            @empty
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400">{{ __('messages.empty_states.conversations') }}</flux:text>
            @endforelse
        </div>
    @endif
</section>
