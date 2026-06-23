<flux:card class="space-y-3">
    @if($this->card)
        <div class="space-y-3">
            <div>
                <flux:heading size="sm">
                    <span class="inline-flex min-w-0 items-center gap-2">
                        <flux:icon name="home-modern" variant="mini" class="size-4 shrink-0 text-sky-500/80 dark:text-sky-300/80" />
                        <span class="min-w-0">{{ $this->card['title'] }}</span>
                    </span>
                </flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ $this->card['type'] }}</flux:text>
            </div>

            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-800">
                    <div class="text-zinc-500">{{ __('properties.card.rooms') }}</div>
                    <div class="font-medium">{{ $this->card['rooms_count'] }}</div>
                </div>
                <div class="rounded-md bg-zinc-50 p-3 dark:bg-zinc-800">
                    <div class="text-zinc-500">{{ __('properties.card.sleeping_places') }}</div>
                    <div class="font-medium">{{ $this->card['sleeping_places_count'] }}</div>
                </div>
            </div>

            <flux:badge icon="home-modern">{{ __('domain.statuses.'.$this->card['status']) }}</flux:badge>
        </div>
    @else
        <flux:text>{{ __('properties.empty.not_found') }}</flux:text>
    @endif
</flux:card>
