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
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.types.'.$this->card['type']) }}</flux:text>
            </div>

            <div class="rounded-md bg-zinc-50 p-3 text-sm dark:bg-zinc-800">
                <div class="text-zinc-500">{{ __('sleeping_places.card.base_price') }}</div>
                <div class="font-medium">{{ __('sleeping_places.card.price_value', ['amount' => $this->card['base_price'], 'currency' => $this->card['currency']]) }}</div>
            </div>

            <flux:badge icon="home-modern">{{ __('domain.statuses.'.$this->card['status']) }}</flux:badge>
        </div>
    @else
        <flux:text>{{ __('sleeping_places.empty.not_found') }}</flux:text>
    @endif
</flux:card>
