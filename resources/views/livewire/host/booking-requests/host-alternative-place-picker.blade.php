<flux:card class="space-y-3">
    <flux:heading size="sm">{{ __('booking_requests.host_view.alternatives') }}</flux:heading>
    <flux:error name="alternative" />
    @forelse($places as $place)
        <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-900">
            <div>
                <flux:text size="sm">{{ $place->display_name ?: $place->title }}</flux:text>
                <flux:text size="xs">{{ $place->currency }} {{ $place->base_price }}</flux:text>
            </div>
            <flux:button type="button" size="sm" variant="outline" wire:click="offer({{ $place->id }})">
                {{ __('booking_requests.actions.offer_alternative_place') }}
            </flux:button>
        </div>
    @empty
        <flux:text>{{ __('booking_requests.empty.no_alternatives') }}</flux:text>
    @endforelse
</flux:card>
