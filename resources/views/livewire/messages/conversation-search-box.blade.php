<section class="space-y-3">
    <flux:field>
        <flux:label>{{ __('messages.actions.search') }}</flux:label>
        <flux:input wire:model.live.debounce.500ms="query" />
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
