<flux:card class="space-y-3">
    @if($this->card)
        <div class="space-y-3">
            <div>
                <flux:heading size="sm">{{ $this->card['title'] }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $this->card['type'] }}</flux:text>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-800">
                    <div class="text-zinc-500">{{ __('rooms.card.sleeping_places') }}</div>
                    <div class="font-medium">{{ $this->card['sleeping_places_count'] }}</div>
                </div>
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-800">
                    <div class="text-zinc-500">{{ __('rooms.card.free_places') }}</div>
                    <div class="font-medium">{{ $this->card['free_sleeping_places_count'] }}</div>
                </div>
            </div>

            <flux:badge>{{ __('domain.statuses.'.$this->card['status']) }}</flux:badge>
        </div>
    @else
        <flux:text>{{ __('rooms.empty.not_found') }}</flux:text>
    @endif
</flux:card>
