<flux:card class="space-y-3">
    @if($this->card)
        <div class="space-y-3">
            <div>
                <flux:heading size="sm">{{ $this->card['title'] }}</flux:heading>
                <flux:text class="text-zinc-600 dark:text-zinc-300">{{ __('sleeping_places.types.'.$this->card['type']) }}</flux:text>
            </div>

            <div class="rounded-md bg-zinc-50 p-3 text-sm dark:bg-zinc-800">
                <div class="text-zinc-500">{{ __('sleeping_places.card.base_price') }}</div>
                <div class="font-medium">{{ __('sleeping_places.card.price_value', ['amount' => $this->card['base_price'], 'currency' => $this->card['currency']]) }}</div>
            </div>

            <flux:badge>{{ __('domain.statuses.'.$this->card['status']) }}</flux:badge>
        </div>
    @else
        <flux:text>{{ __('sleeping_places.empty.not_found') }}</flux:text>
    @endif
</flux:card>
