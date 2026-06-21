<div class="space-y-4">
    <div class="space-y-1">
        <flux:heading>{{ __('calendar.host.sleeping_place_title') }}</flux:heading>
        <flux:text>{{ __('calendar.host.sleeping_place_helper') }}</flux:text>
    </div>

    <div class="space-y-3">
        @forelse($this->cards as $card)
            <flux:card class="space-y-2">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <flux:heading size="sm">{{ $card['date'] }}</flux:heading>
                        <flux:text size="sm">{{ $card['title'] }}</flux:text>
                    </div>
                    <flux:badge>{{ $card['status_label'] }}</flux:badge>
                </div>
            </flux:card>
        @empty
            <flux:card>
                <flux:text>{{ __('calendar.empty.days') }}</flux:text>
            </flux:card>
        @endforelse
    </div>
</div>
